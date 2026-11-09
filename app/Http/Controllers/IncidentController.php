<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Incident;
use App\Models\IncidentLog;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IncidentController extends Controller
{
    /**
     * Muestra la consola de tickets y servicios de mantenimiento de hardware.
     * Basado en: docs/stitch_asset_loan_maintenance_system/tickets_y_servicios_de_mantenimiento
     */
    public function index(Request $request): View
    {
        $visibleIncidents = Incident::query();
        if (Auth::user()->isTecnico()) {
            $visibleIncidents->where('assigned_technician_id', Auth::id());
        }

        $query = Incident::with(['equipment.category', 'technician', 'reportedBy', 'logs']);
        if (Auth::user()->isTecnico()) {
            $query->where('assigned_technician_id', Auth::id());
        }

        // 1. Filtro por estatus de atención
        if ($status = $request->input('status')) {
            if ($status === 'taller') {
                $query->whereIn('status', ['reportado', 'asignado', 'en_diagnostico', 'en_reparacion']);
            } else {
                $query->where('status', $status);
            }
        }

        // 2. Filtro por prioridad o urgencia
        if ($priority = $request->input('priority')) {
            $query->where('priority', $priority);
        }

        // 3. Filtro por tipo de servicio
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        // 4. Filtro por técnico asignado
        if ($technicianId = $request->input('technician_id')) {
            $query->where('assigned_technician_id', $technicianId);
        }

        // 5. Búsqueda por código de ticket, equipo o palabras clave
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhereHas('equipment', function ($eq) use ($search) {
                      $eq->where('inventory_code', 'like', "%{$search}%")
                         ->orWhere('serial_number', 'like', "%{$search}%")
                         ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        // Contadores de cabecera
        $counts = [
            'total' => (clone $visibleIncidents)->count(),
            'taller' => (clone $visibleIncidents)->whereIn('status', ['reportado', 'asignado', 'en_diagnostico', 'en_reparacion'])->count(),
            'criticos' => (clone $visibleIncidents)->whereIn('priority', ['alta', 'critica'])->whereNotIn('status', ['resuelto', 'cancelado'])->count(),
            'resueltos' => (clone $visibleIncidents)->where('status', 'resuelto')->count(),
        ];

        $incidents = $query->orderByRaw("CASE priority WHEN 'critica' THEN 1 WHEN 'alta' THEN 2 WHEN 'media' THEN 3 ELSE 4 END")
            ->latest('reported_at')
            ->paginate(10)
            ->withQueryString();

        $technicians = User::where('role', User::ROLE_TECNICO)
            ->where('status', 'activo')
            ->orderBy('name')
            ->get();

        $equipmentList = Equipment::where('status', '!=', 'baja')
            ->orderBy('name')
            ->get();

        return view('private.incidents.index', compact('incidents', 'counts', 'technicians', 'equipmentList'));
    }

    /**
     * Levanta un nuevo ticket de servicio o soporte técnico para un activo.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'equipment_id' => ['required', 'exists:equipment,id'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string'],
            'type' => ['required', 'in:hardware,software,daño_fisico,desgaste_natural,perdida_accesorio,mantenimiento_preventivo'],
            'priority' => ['required', 'in:baja,media,alta,critica'],
            'assigned_technician_id' => [
                'nullable',
                Rule::exists('users', 'id')->where('role', User::ROLE_TECNICO)->where('status', 'activo'),
            ],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
        ], [
            'equipment_id.required' => 'Debe asociar un activo patrimonial al ticket.',
            'title.required' => 'Especifique el título o síntoma general de la falla.',
            'description.required' => 'La descripción detallada del problema es requerida.',
        ]);

        $year = date('Y');
        $nextNumber = Incident::whereYear('created_at', $year)->count() + 1;
        $ticketCode = "TK-{$year}-" . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            $operatorId = Auth::id();
            $status = !empty($validated['assigned_technician_id']) ? 'asignado' : 'reportado';

            $incident = Incident::create([
                'ticket_code' => $ticketCode,
                'equipment_id' => $validated['equipment_id'],
                'reported_by_id' => $operatorId,
                'assigned_technician_id' => $validated['assigned_technician_id'] ?? null,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'type' => $validated['type'],
                'priority' => $validated['priority'],
                'status' => $status,
                'estimated_cost' => $validated['estimated_cost'] ?? 0.00,
                'reported_at' => now(),
            ]);

            // Mover el equipo a estatus en_mantenimiento
            $eq = Equipment::findOrFail($validated['equipment_id']);
            $eq->update(['status' => 'en_mantenimiento']);

            // Crear bitácora inicial del ticket
            IncidentLog::create([
                'incident_id' => $incident->id,
                'technician_id' => $operatorId,
                'activity' => 'Apertura de orden de servicio y diagnóstico inicial',
                'notes' => $validated['description'],
                'status_before' => 'disponible',
                'status_after' => $status,
            ]);

            DB::commit();

            AuditService::log(
                'Incident',
                $incident->id,
                'TICKET_CREADO',
                "Ticket {$ticketCode} levantado para {$eq->inventory_code} - {$incident->title} [Prioridad: {$incident->priority}]",
                $validated,
                in_array($incident->priority, ['alta', 'critica']) ? 'advertencia' : 'normal'
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Ticket {$ticketCode} creado correctamente.",
                    'incident' => $incident->load('equipment'),
                ]);
            }

            return redirect()->route('incidents.index')->with('success', "Ticket {$ticketCode} registrado en taller.");
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withInput()->with('error', 'Error al levantar el ticket: ' . $e->getMessage());
        }
    }

    /**
     * Muestra la hoja de inspección y registro técnico de un ticket.
     */
    public function show(Incident $incident): View|JsonResponse
    {
        $this->ensureTechnicianAssignment($incident);

        $incident->load([
            'equipment.category',
            'technician',
            'reportedBy',
            'logs.technician',
            'loanItem.loan.user'
        ]);

        $technicians = User::where('role', User::ROLE_TECNICO)
            ->where('status', 'activo')
            ->orderBy('name')
            ->get();

        if (request()->wantsJson()) {
            return response()->json($incident);
        }

        return view('private.incidents.show', compact('incident', 'technicians'));
    }

    /**
     * Asigna o reasigna un técnico especialista al ticket.
     */
    public function assignTechnician(Request $request, Incident $incident): RedirectResponse
    {
        $request->validate([
            'assigned_technician_id' => ['required', 'exists:users,id'],
        ]);

        $tech = User::findOrFail($request->assigned_technician_id);
        $statusBefore = $incident->status;
        $statusAfter = in_array($incident->status, ['reportado']) ? 'asignado' : $incident->status;

        $incident->update([
            'assigned_technician_id' => $tech->id,
            'status' => $statusAfter,
        ]);

        IncidentLog::create([
            'incident_id' => $incident->id,
            'technician_id' => Auth::id(),
            'activity' => "Reasignación técnica a: {$tech->name}",
            'notes' => "Asignado por " . Auth::user()->name,
            'status_before' => $statusBefore,
            'status_after' => $statusAfter,
        ]);

        AuditService::log('Incident', $incident->id, 'TICKET_ASIGNADO', "Ticket {$incident->ticket_code} asignado a {$tech->name}", null, 'normal');

        return back()->with('success', "Ticket {$incident->ticket_code} asignado al técnico {$tech->name}.");
    }

    /**
     * Agrega un avance o bitácora de actividad técnica al ticket.
     */
    public function addProgressLog(Request $request, Incident $incident): RedirectResponse
    {
        $this->ensureTechnicianAssignment($incident);

        $validated = $request->validate([
            'activity' => ['required', 'string', 'max:150'],
            'notes' => ['nullable', 'string'],
            'status_after' => ['nullable', 'in:asignado,en_diagnostico,en_reparacion'],
        ]);

        $statusBefore = $incident->status;
        $statusAfter = $validated['status_after'] ?? $statusBefore;

        DB::beginTransaction();
        try {
            IncidentLog::create([
                'incident_id' => $incident->id,
                'technician_id' => Auth::id(),
                'activity' => $validated['activity'],
                'notes' => $validated['notes'] ?? null,
                'status_before' => $statusBefore,
                'status_after' => $statusAfter,
            ]);

            if ($statusAfter !== $statusBefore) {
                $incident->update(['status' => $statusAfter]);
            }

            DB::commit();

            AuditService::log('Incident', $incident->id, 'TICKET_AVANCE', "Avance registrado en {$incident->ticket_code}: {$validated['activity']}", $validated, 'normal');

            return back()->with('success', 'Avance técnico asentado en la bitácora del ticket.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->with('error', 'Error al registrar el avance técnico: ' . $e->getMessage());
        }
    }

    /**
     * Dictamina la solución definitiva del ticket y libera el activo.
     */
    public function resolve(Request $request, Incident $incident): RedirectResponse
    {
        $this->ensureTechnicianAssignment($incident);

        $validated = $request->validate([
            'diagnostic' => ['required', 'string'],
            'solution_applied' => ['required', 'string'],
            'actual_cost' => ['nullable', 'numeric', 'min:0'],
            'equipment_status' => [
                'required',
                Auth::user()->isTecnico() ? Rule::in(['disponible']) : Rule::in(['disponible', 'baja']),
            ],
            'physical_condition' => ['required', 'in:excelente,bueno,regular,malo'],
        ]);

        DB::beginTransaction();
        try {
            $isBaja = ($validated['equipment_status'] === 'baja');
            $incidentStatus = $isBaja ? 'baja_definitiva' : 'resuelto';

            $incident->update([
                'status' => $incidentStatus,
                'diagnostic' => $validated['diagnostic'],
                'solution_applied' => $validated['solution_applied'],
                'actual_cost' => $validated['actual_cost'] ?? 0.00,
                'resolved_at' => now(),
            ]);

            // Actualizar equipo
            $incident->equipment->update([
                'status' => $validated['equipment_status'],
                'physical_condition' => $validated['physical_condition'],
                'notes' => trim($incident->equipment->notes . "\n[MANTTO " . now()->format('Y-m-d') . "]: Solución: " . $validated['solution_applied']),
            ]);

            // Asentar cierre en incident_logs
            IncidentLog::create([
                'incident_id' => $incident->id,
                'technician_id' => Auth::id(),
                'activity' => "Resolución y cierre formal del ticket",
                'notes' => "Dictamen: {$validated['solution_applied']}. Costo final: \${$incident->actual_cost}",
                'status_before' => $incident->getOriginal('status'),
                'status_after' => $incidentStatus,
            ]);

            DB::commit();

            AuditService::log(
                'Incident',
                $incident->id,
                'TICKET_RESUELTO',
                "Ticket {$incident->ticket_code} resuelto para activo {$incident->equipment->inventory_code}. Resultado: {$incidentStatus}",
                $validated,
                $isBaja ? 'advertencia' : 'normal'
            );

            return redirect()->route('incidents.index')->with('success', "Ticket {$incident->ticket_code} cerrado exitosamente. Activo actualizado.");
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->with('error', 'Error al resolver el ticket: ' . $e->getMessage());
        }
    }

    private function ensureTechnicianAssignment(Incident $incident): void
    {
        if (Auth::user()->isTecnico() && $incident->assigned_technician_id !== Auth::id()) {
            abort(403, 'Solo puedes consultar y actualizar tickets asignados a tu cuenta.');
        }
    }
}


<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Incident;
use App\Models\IncidentLog;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LoanController extends Controller
{
    /**
     * Muestra la consola de control detallado de préstamos y devoluciones.
     * Basado en: docs/stitch_asset_loan_maintenance_system/control_detallado_de_pr_stamos_y_devoluciones
     */
    public function index(Request $request): View
    {
        $query = Loan::with(['user', 'items.equipment', 'authorizedBy', 'dispatchedBy', 'receivedBy']);

        // 1. Filtro por estatus de ciclo de vida
        if ($status = $request->input('status')) {
            if ($status === 'vencido') {
                $query->where('status', 'entregado')
                      ->where('expected_return_date', '<', now());
            } else {
                $query->where('status', $status);
            }
        }

        // 2. Búsqueda por folio o usuario custodio
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('folio', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('institutional_id', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // 3. Filtro por usuario específico
        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        // Conteo rápido de estatus
        $counts = [
            'total' => Loan::count(),
            'solicitados' => Loan::where('status', 'solicitado')->count(),
            'activos' => Loan::where('status', 'entregado')->count(),
            'vencidos' => Loan::where('status', 'entregado')->where('expected_return_date', '<', now())->count(),
            'devueltos' => Loan::whereIn('status', ['devuelto', 'devuelto_con_observacion'])->count(),
        ];

        $loans = $query->latest('request_date')->paginate(10)->withQueryString();
        
        // Usuarios que pueden recibir préstamos (sin sanciones activas)
        $eligibleUsers = User::where('role', User::ROLE_USUARIO)
            ->where('status', 'activo')
            ->orderBy('name')
            ->get();

        $selectedBorrowerId = $eligibleUsers->firstWhere('id', $request->input('new_user_id'))?->id;

        // Equipos disponibles para préstamo
        $availableEquipments = Equipment::where('status', 'disponible')
            ->with('category')
            ->orderBy('name')
            ->get();

        return view('private.loans.index', compact('loans', 'counts', 'eligibleUsers', 'availableEquipments', 'selectedBorrowerId'));
    }

    /**
     * Registra un nuevo contrato o solicitud de préstamo de activos.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where('role', User::ROLE_USUARIO)->where('status', 'activo'),
            ],
            'equipment_ids' => ['required', 'array', 'min:1'],
            'equipment_ids.*' => ['exists:equipment,id'],
            'purpose' => ['required', 'string', 'max:500'],
            'location_of_use' => ['nullable', 'string', 'max:120'],
            'expected_return_date' => ['required', 'date', 'after:now'],
            'accessories_included' => ['nullable', 'string', 'max:255'],
            'condition_on_loan' => ['nullable', 'in:excelente,bueno,regular'],
            'notes_on_loan' => ['nullable', 'string'],
            'immediate_dispatch' => ['nullable', 'boolean'],
        ], [
            'equipment_ids.required' => 'Debe seleccionar al menos un activo para el préstamo.',
            'expected_return_date.after' => 'La fecha esperada de devolución debe ser posterior al momento actual.',
        ]);

        $borrower = User::findOrFail($validated['user_id']);

        // Verificación de sanciones activas
        if (!$borrower->canBorrow()) {
            return back()->withInput()->with('error', "El usuario {$borrower->name} cuenta con sanciones activas o suspensión temporal. No es posible generar el préstamo.");
        }

        // Generación de Folio Institucional: PR-YYYY-####
        $year = date('Y');
        $nextNumber = Loan::whereYear('created_at', $year)->count() + 1;
        $folio = "PR-{$year}-" . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            $isImmediate = $request->boolean('immediate_dispatch');
            $operatorId = Auth::id();

            $loan = Loan::create([
                'folio' => $folio,
                'user_id' => $borrower->id,
                'authorized_by_id' => $isImmediate ? $operatorId : null,
                'dispatched_by_id' => $isImmediate ? $operatorId : null,
                'purpose' => $validated['purpose'],
                'location_of_use' => $validated['location_of_use'] ?? 'Campus Central / Aulas',
                'request_date' => now(),
                'loan_date' => $isImmediate ? now() : null,
                'expected_return_date' => $validated['expected_return_date'],
                'status' => $isImmediate ? 'entregado' : 'solicitado',
                'observations' => $validated['notes_on_loan'] ?? null,
            ]);

            // Vincular los ítems del préstamo
            foreach ($validated['equipment_ids'] as $eqId) {
                $eq = Equipment::lockForUpdate()->findOrFail($eqId);

                // Verificar disponibilidad
                if ($isImmediate && $eq->status !== 'disponible') {
                    throw new \Exception("El equipo {$eq->inventory_code} ({$eq->name}) no se encuentra disponible actualmente.");
                }

                LoanItem::create([
                    'loan_id' => $loan->id,
                    'equipment_id' => $eq->id,
                    'condition_on_loan' => $validated['condition_on_loan'] ?? $eq->physical_condition,
                    'accessories_included' => $validated['accessories_included'] ?? 'Cargador original y accesorios básicos',
                    'notes_on_loan' => $validated['notes_on_loan'] ?? null,
                ]);

                if ($isImmediate) {
                    $eq->update(['status' => 'prestado']);
                }
            }

            DB::commit();

            AuditService::log(
                'Loan',
                $loan->id,
                $isImmediate ? 'PRESTAMO_ENTREGADO' : 'PRESTAMO_SOLICITADO',
                "Acta {$loan->folio} registrada para {$borrower->name}. Total activos: " . count($validated['equipment_ids']),
                ['folio' => $loan->folio, 'custodio' => $borrower->name, 'inmediato' => $isImmediate],
                'normal'
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Préstamo {$loan->folio} generado con éxito.",
                    'loan' => $loan->load('items.equipment'),
                ]);
            }

            return redirect()->route('loans.index')->with('success', "Acta de préstamo {$loan->folio} registrada correctamente.");
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withInput()->with('error', 'Error al procesar el préstamo: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el detalle del acta de préstamo (Acta Digital Folio).
     */
    public function show(Loan $loan): View|JsonResponse
    {
        $loan->load([
            'user',
            'items.equipment.category',
            'authorizedBy',
            'dispatchedBy',
            'receivedBy',
            'sanctions'
        ]);

        if (request()->wantsJson()) {
            return response()->json($loan);
        }

        return view('private.loans.show', compact('loan'));
    }

    /**
     * Autoriza formalmente una solicitud de préstamo pendiente.
     */
    public function approve(Request $request, Loan $loan): RedirectResponse
    {
        if ($loan->status !== 'solicitado') {
            return back()->with('error', 'Solo pueden autorizarse préstamos en estado solicitado.');
        }

        $loan->update([
            'status' => 'aprobado',
            'authorized_by_id' => Auth::id(),
        ]);

        AuditService::log('Loan', $loan->id, 'PRESTAMO_AUTORIZADO', "Préstamo {$loan->folio} autorizado por " . Auth::user()->name, null, 'normal');

        return back()->with('success', "Préstamo {$loan->folio} autorizado. Listo para entrega en ventanilla.");
    }

    /**
     * Rechaza una solicitud de préstamo pendiente con motivo justificado.
     */
    public function reject(Request $request, Loan $loan): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ], [
            'rejection_reason.required' => 'Debe especificar el motivo del rechazo del préstamo.',
        ]);

        $loan->update([
            'status' => 'rechazado',
            'authorized_by_id' => Auth::id(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        AuditService::log('Loan', $loan->id, 'PRESTAMO_RECHAZADO', "Préstamo {$loan->folio} rechazado: {$request->rejection_reason}", null, 'advertencia');

        return back()->with('success', "Solicitud {$loan->folio} rechazada.");
    }

    /**
     * Realiza la entrega física de los equipos en ventanilla.
     */
    public function dispatch(Request $request, Loan $loan): RedirectResponse
    {
        if (!in_array($loan->status, ['solicitado', 'aprobado'])) {
            return back()->with('error', 'El préstamo no se encuentra listo para entrega.');
        }

        DB::beginTransaction();
        try {
            $loan->update([
                'status' => 'entregado',
                'dispatched_by_id' => Auth::id(),
                'loan_date' => now(),
            ]);

            // Poner los equipos como prestados
            foreach ($loan->items as $item) {
                $item->equipment->update(['status' => 'prestado']);
            }

            DB::commit();

            AuditService::log('Loan', $loan->id, 'PRESTAMO_DESPACHADO', "Equipos entregados físicamente para el folio {$loan->folio}", null, 'normal');

            return back()->with('success', "Activos entregados al custodio. Préstamo {$loan->folio} activo.");
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->with('error', 'Error al despachar el equipo: ' . $e->getMessage());
        }
    }

    /**
     * Registra la recepción física y devolución de los equipos prestados.
     * Evalúa condición de retorno e ingresa tickets si se detectan anomalías.
     */
    public function receiveReturn(Request $request, Loan $loan): RedirectResponse
    {
        if ($loan->status !== 'entregado') {
            return back()->with('error', 'Solo es posible registrar devolución sobre préstamos actualmente activos y entregados.');
        }

        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.loan_item_id' => ['required', 'exists:loan_items,id'],
            'items.*.condition_on_return' => ['required', 'in:excelente,bueno,regular,dañado,incompleto'],
            'items.*.notes_on_return' => ['nullable', 'string'],
        ]);

        DB::beginTransaction();
        try {
            $hasIncidents = false;
            $operatorId = Auth::id();

            foreach ($validated['items'] as $itemData) {
                $loanItem = LoanItem::with('equipment')->findOrFail($itemData['loan_item_id']);
                
                $condition = $itemData['condition_on_return'];
                $notes = $itemData['notes_on_return'] ?? 'Devolución rutinaria sin observaciones';

                $loanItem->update([
                    'condition_on_return' => $condition,
                    'notes_on_return' => $notes,
                    'returned_at' => now(),
                ]);

                // Si viene dañado o incompleto, enviar a taller técnico automáticamente
                if (in_array($condition, ['dañado', 'incompleto'])) {
                    $hasIncidents = true;

                    // Cambiar estatus de equipo a en_mantenimiento
                    $loanItem->equipment->update([
                        'status' => 'en_mantenimiento',
                        'physical_condition' => ($condition === 'dañado') ? 'malo' : 'regular',
                    ]);

                    // Crear Ticket de Mantenimiento Automático
                    $ticketCode = 'TK-' . date('Y') . '-' . str_pad(Incident::count() + 1, 4, '0', STR_PAD_LEFT);
                    $incident = Incident::create([
                        'ticket_code' => $ticketCode,
                        'equipment_id' => $loanItem->equipment_id,
                        'loan_item_id' => $loanItem->id,
                        'reported_by_id' => $operatorId,
                        'title' => "Falla reportada en devolución de préstamo {$loan->folio}",
                        'description' => "El equipo retornó con estatus [{$condition}]. Observación técnica: {$notes}",
                        'type' => ($condition === 'dañado') ? 'daño_fisico' : 'perdida_accesorio',
                        'priority' => 'alta',
                        'status' => 'reportado',
                        'reported_at' => now(),
                    ]);

                    IncidentLog::create([
                        'incident_id' => $incident->id,
                        'technician_id' => $operatorId,
                        'activity' => 'Apertura automática por anomalía en recepción de préstamo',
                        'notes' => "Acta {$loan->folio}. Condición observada: {$condition}.",
                        'status_before' => 'prestado',
                        'status_after' => 'en_mantenimiento',
                    ]);

                    AuditService::log('Incident', $incident->id, 'TICKET_AUTO_APERTURA', "Ticket {$ticketCode} generado por avería en devolución de {$loan->folio}", null, 'advertencia');
                } else {
                    // Equipo devuelto íntegro: reintegrar a disponibilidad inmediata
                    $loanItem->equipment->update([
                        'status' => 'disponible',
                        'physical_condition' => $condition,
                    ]);
                }
            }

            // Marcar estado global del préstamo
            $finalStatus = $hasIncidents ? 'devuelto_con_observacion' : 'devuelto';
            $loan->update([
                'status' => $finalStatus,
                'actual_return_date' => now(),
                'received_by_id' => $operatorId,
            ]);

            DB::commit();

            AuditService::log(
                'Loan',
                $loan->id,
                'PRESTAMO_DEVUELTO',
                "Devolución concluida para acta {$loan->folio}. Estatus: {$finalStatus}",
                ['folio' => $loan->folio, 'anomalias' => $hasIncidents],
                $hasIncidents ? 'advertencia' : 'normal'
            );

            $msg = $hasIncidents
                ? "Devolución registrada con observaciones. Se aperturaron tickets técnicos para los activos averiados."
                : "Devolución de activos completada con éxito. Los equipos están disponibles en inventario.";

            return redirect()->route('loans.index')->with('success', $msg);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->with('error', 'Error al procesar la devolución: ' . $e->getMessage());
        }
    }
}


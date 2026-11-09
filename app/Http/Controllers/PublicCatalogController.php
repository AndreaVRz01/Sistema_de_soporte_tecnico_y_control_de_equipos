<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PublicCatalogController extends Controller
{
    /**
     * Muestra el portal público de consulta de activos y catálogo institucional.
     * Basado en: docs/stitch_asset_loan_maintenance_system/portal_p_blico_y_cat_logo_de_equipos
     */
    public function index(Request $request): View
    {
        $query = Equipment::with('category')->where('status', '!=', 'baja');

        // 1. Filtro por Categoría
        if ($catCode = $request->input('category')) {
            $query->whereHas('category', function ($cq) use ($catCode) {
                $cq->where('code', $catCode)->orWhere('id', $catCode);
            });
        }

        // 2. Filtro de Búsqueda
        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('inventory_code', 'like', "%{$search}%")
                  ->orWhere('specifications', 'like', "%{$search}%");
            });
        }

        // 3. Filtro solo disponibles
        if ($request->boolean('available_only')) {
            $query->where('status', 'disponible');
        }

        $equipment = $query->orderByRaw("CASE status WHEN 'disponible' THEN 1 WHEN 'reservado' THEN 2 ELSE 3 END")
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = Category::where('is_active', true)
            ->withCount(['equipment', 'availableEquipment'])
            ->get();

        return view('public.catalog', compact('equipment', 'categories'));
    }

    /**
     * Ficha técnica pública de un activo.
     */
    public function show(Equipment $equipment): JsonResponse
    {
        $equipment->load('category');

        return response()->json([
            'id' => $equipment->id,
            'name' => $equipment->name,
            'inventory_code' => $equipment->inventory_code,
            'brand' => $equipment->brand,
            'model' => $equipment->model,
            'specifications' => $equipment->specifications,
            'status' => $equipment->status,
            'physical_condition' => $equipment->physical_condition,
            'location' => $equipment->location,
            'category_name' => $equipment->category->name ?? 'General',
        ]);
    }

    /**
     * Permite a un miembro de la comunidad universitaria solicitar un préstamo desde el catálogo público.
     */
    public function requestLoan(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'institutional_id' => ['required', 'string'],
            'email' => ['required', 'email'],
            'equipment_id' => ['required', 'exists:equipment,id'],
            'purpose' => ['required', 'string', 'max:500'],
            'expected_return_date' => ['required', 'date', 'after:now'],
            'location_of_use' => ['nullable', 'string', 'max:120'],
        ], [
            'institutional_id.required' => 'Ingrese su matrícula de estudiante o clave de docente.',
            'email.required' => 'El correo institucional es obligatorio.',
            'equipment_id.required' => 'Seleccione el activo deseado.',
            'expected_return_date.after' => 'La fecha acordada de devolución debe ser futura.',
        ]);

        $user = User::where('institutional_id', $validated['institutional_id'])
            ->where('email', $validated['email'])
            ->first();

        if (!$user) {
            return back()->withInput()->with('error', 'Los datos institucionales (matrícula y correo) no coinciden con ningún usuario registrado en la universidad.');
        }

        if (!$user->canBorrow()) {
            return back()->withInput()->with('error', "El usuario {$user->name} presenta sanciones disciplinarias activas. Solicitud denegada.");
        }

        $equipment = Equipment::findOrFail($validated['equipment_id']);
        if ($equipment->status !== 'disponible') {
            return back()->withInput()->with('error', "El equipo {$equipment->name} no se encuentra disponible para préstamo en este momento.");
        }

        // Generar folio de solicitud
        $year = date('Y');
        $nextNumber = Loan::whereYear('created_at', $year)->count() + 1;
        $folio = "PR-{$year}-" . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            $loan = Loan::create([
                'folio' => $folio,
                'user_id' => $user->id,
                'purpose' => $validated['purpose'],
                'location_of_use' => $validated['location_of_use'] ?? 'Campus Universitario',
                'request_date' => now(),
                'expected_return_date' => $validated['expected_return_date'],
                'status' => 'solicitado',
                'observations' => 'Solicitud generada a través del portal público institucional.',
            ]);

            LoanItem::create([
                'loan_id' => $loan->id,
                'equipment_id' => $equipment->id,
                'condition_on_loan' => $equipment->physical_condition,
                'accessories_included' => 'Equipamiento estándar del activo',
                'notes_on_loan' => 'Solicitud web',
            ]);

            DB::commit();

            AuditService::log(
                'Loan',
                $loan->id,
                'PRESTAMO_WEB_SOLICITADO',
                "Solicitud de préstamo web {$loan->folio} registrada por {$user->name} para activo {$equipment->inventory_code}",
                ['folio' => $loan->folio, 'solicitante' => $user->name, 'equipo' => $equipment->inventory_code],
                'normal',
                $user->id
            );
            app(\App\Services\ActivityLogService::class)->record($user, 'prestamo.solicitado', $loan, [
                'folio' => $loan->folio,
                'equipment_id' => $equipment->id,
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'folio' => $loan->folio,
                    'message' => "Su solicitud con folio {$loan->folio} fue registrada. Presente su credencial en ventanilla una vez autorizada.",
                ]);
            }

            return redirect()->route('public.catalog')->with('success', "¡Solicitud recibida! Folio asignado: {$loan->folio}. Acuda a ventanilla una vez aprobada.");
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withInput()->with('error', 'Error al procesar la solicitud: ' . $e->getMessage());
        }
    }
}


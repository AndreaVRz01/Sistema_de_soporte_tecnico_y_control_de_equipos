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
     * Nombres amigables para las categorías (sin tecnicismos).
     * Se usan en sugerencias y en la vista del catálogo.
     */
    public static array $friendlyCategoryNames = [
        'LAP'  => 'Laptops y Computadoras',
        'PROY' => 'Proyectores y Pantallas',
        'AUD'  => 'Bocinas y Micrófonos',
        'RED'  => 'Redes y Conectividad',
        'PER'  => 'Accesorios y Periféricos',
    ];

    /**
     * Muestra el portal público de consulta de activos y catálogo institucional.
     */
    public function index(Request $request): View
    {
        $query = Equipment::available()->with('category');

        // 1. Filtro por Categoría
        $catCode = $request->input('category');
        if (is_string($catCode) && $catCode !== '') {
            $query->whereHas('category', function ($cq) use ($catCode) {
                $cq->where('code', $catCode);

                if (ctype_digit($catCode)) {
                    $cq->orWhere('id', (int) $catCode);
                }
            });
        }

        // 2. Filtro de Búsqueda (sólo si viene texto, insensible a mayúsculas/minúsculas)
        $search = trim((string) $request->input('q', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('brand', 'ilike', "%{$search}%")
                  ->orWhere('model', 'ilike', "%{$search}%")
                  ->orWhere('inventory_code', 'ilike', "%{$search}%")
                  ->orWhere('specifications', 'ilike', "%{$search}%");
            });
        }

        $equipment = $query->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = Category::where('is_active', true)
            ->withCount(['equipment', 'availableEquipment'])
            ->get();
        $availableEquipmentCount = Equipment::available()->count();
        $friendlyNames = self::$friendlyCategoryNames;

        return view('public.catalog', compact('equipment', 'categories', 'availableEquipmentCount', 'friendlyNames'));
    }

    /**
     * Endpoint AJAX: devuelve sugerencias de búsqueda en tiempo real.
     * GET /catalogo/sugerencias?q=laptop
     */
    public function suggestions(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $results = Equipment::available()
            ->with('category')
            ->where(function ($query) use ($q) {
                $query->where('name', 'ilike', "%{$q}%")
                      ->orWhere('brand', 'ilike', "%{$q}%")
                      ->orWhere('model', 'ilike', "%{$q}%")
                      ->orWhere('inventory_code', 'ilike', "%{$q}%");
            })
            ->select('id', 'name', 'brand', 'model', 'inventory_code', 'category_id', 'image_url')
            ->limit(8)
            ->get()
            ->map(fn ($item) => [
                'id'             => $item->id,
                'name'           => $item->name,
                'brand'          => $item->brand,
                'model'          => $item->model,
                'inventory_code' => $item->inventory_code,
                'category_name'  => self::$friendlyCategoryNames[$item->category?->code ?? ''] ?? ($item->category?->name ?? 'Equipo'),
                'category_code'  => $item->category?->code ?? '',
                'image_url'      => $item->image_url,
            ]);

        return response()->json($results);
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
        $user = $request->user();
        abort_unless(
            $user instanceof User
                && $user->isUsuario()
                && $user->status === 'activo'
                && $user->registration_status === User::REGISTRATION_APPROVED,
            403
        );

        $validated = $request->validate([
            'equipment_id' => ['required', 'exists:equipment,id'],
            'purpose' => ['required', 'string', 'max:500'],
            'expected_return_date' => ['required', 'date', 'after:now'],
            'location_of_use' => ['nullable', 'string', 'max:120'],
        ], [
            'equipment_id.required' => 'Seleccione el activo deseado.',
            'expected_return_date.after' => 'La fecha acordada de devolución debe ser futura.',
        ]);

        if (!$user->canBorrow()) {
            return back()->withInput()->with('error', "El usuario {$user->name} presenta sanciones disciplinarias activas. Solicitud denegada.");
        }

        $equipment = Equipment::available()->find($validated['equipment_id']);
        if (!$equipment) {
            return back()->withInput()->with('error', 'El equipo seleccionado ya no está disponible para préstamo.');
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


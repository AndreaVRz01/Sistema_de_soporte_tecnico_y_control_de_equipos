<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\User;
use App\Services\AuditService;
use App\Services\EquipmentSearchService;
use Illuminate\Database\Eloquent\Builder;
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
    public function index(Request $request, EquipmentSearchService $searchService): View
    {
        $query = Equipment::available()->with('category');
        $isFuzzySearch = false;

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

        // 2. Filtro de búsqueda por datos del activo y su categoría.
        $search = trim($request->validate(['q' => ['nullable', 'string', 'max:100']])['q'] ?? '');
        if ($search !== '') {
            $exactQuery = clone $query;
            $this->applySearch($exactQuery, $search);

            if ($exactQuery->exists()) {
                $query = $exactQuery;
            } else {
                $similar = $searchService->similar($search, availableOnly: true);
                $query->whereIn('id', $similar->pluck('id'));
                $isFuzzySearch = $similar->isNotEmpty() && $query->exists();
            }
        }

        $equipment = $query->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = Category::where('is_active', true)
            ->withCount(['equipment', 'availableEquipment'])
            ->get();
        $availableEquipmentCount = Equipment::available()->count();
        $friendlyNames = self::$friendlyCategoryNames;

        return view('public.catalog', compact('equipment', 'categories', 'availableEquipmentCount', 'friendlyNames', 'isFuzzySearch'));
    }

    /**
     * Endpoint AJAX: devuelve sugerencias de búsqueda en tiempo real.
     * GET /catalogo/sugerencias?q=laptop
     */
    public function suggestions(Request $request, EquipmentSearchService $searchService): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:50'],
        ]);
        $q = trim($validated['q'] ?? '');

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $category = trim($validated['category'] ?? '');
        $categoryId = $category === ''
            ? null
            : (is_numeric($category)
                ? (int) $category
                : (Category::where('code', $category)->value('id') ?? -1));
        $exactQuery = Equipment::available()->with('category');
        if ($category !== '') {
            $exactQuery->whereHas('category', function (Builder $query) use ($category) {
                $query->where('code', $category);

                if (ctype_digit($category)) {
                    $query->orWhere('id', (int) $category);
                }
            });
        }
        $this->applySearch($exactQuery, $q);
        $exact = $exactQuery->orderBy('name')->limit(8)->get();
        $similar = $exact->isEmpty()
            ? $searchService->similar(
                $q,
                availableOnly: true,
                categoryId: $categoryId
            )
            : collect();

        $results = $exact->concat($similar)->take(8)
            ->map(fn ($item) => [
                'id'             => $item->id,
                'name'           => $item->name,
                'brand'          => $item->brand,
                'model'          => $item->model,
                'inventory_code' => $item->inventory_code,
                'category_name'  => self::$friendlyCategoryNames[$item->category?->code ?? ''] ?? ($item->category?->name ?? 'Equipo'),
                'category_code'  => $item->category?->code ?? '',
                'is_similar'     => !$exact->contains('id', $item->id),
            ]);

        return response()->json($results);
    }

    private function applySearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $matches) use ($search) {
            $pattern = "%{$search}%";
            $matches->whereRaw('LOWER(name) LIKE LOWER(?)', [$pattern])
                ->orWhereRaw('LOWER(brand) LIKE LOWER(?)', [$pattern])
                ->orWhereRaw('LOWER(model) LIKE LOWER(?)', [$pattern])
                ->orWhereRaw('LOWER(inventory_code) LIKE LOWER(?)', [$pattern])
                ->orWhereRaw('LOWER(specifications) LIKE LOWER(?)', [$pattern])
                ->orWhereHas('category', fn (Builder $category) => $category
                    ->whereRaw('LOWER(name) LIKE LOWER(?)', [$pattern])
                    ->orWhereRaw('LOWER(code) LIKE LOWER(?)', [$pattern]));
        });
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


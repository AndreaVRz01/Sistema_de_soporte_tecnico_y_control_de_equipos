<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Equipment;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EquipmentController extends Controller
{
    /**
     * Listado maestro de activos con filtros multifactoriales.
     * Basado en: docs/stitch_asset_loan_maintenance_system/control_de_activos_y_pr_stamos_crud
     */
    public function index(Request $request): View
    {
        $query = Equipment::with(['category', 'openIncidents']);

        // 1. Búsqueda por texto (Nombre, Serie, Código de Activo, Marca o Modelo)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('inventory_code', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%");
            });
        }

        // 2. Filtro por Categoría
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // 3. Filtro por Estatus Operativo
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // 4. Filtro por Condición Física
        if ($condition = $request->input('physical_condition')) {
            $query->where('physical_condition', $condition);
        }

        // 5. Filtro por Ubicación
        if ($location = $request->input('location')) {
            $query->where('location', $location);
        }

        // Conteo rápido para insignias de estado
        $counts = [
            'total' => Equipment::count(),
            'disponible' => Equipment::where('status', 'disponible')->count(),
            'prestado' => Equipment::where('status', 'prestado')->count(),
            'en_mantenimiento' => Equipment::where('status', 'en_mantenimiento')->count(),
            'baja' => Equipment::where('status', 'baja')->count(),
        ];

        $equipment = $query->latest()->paginate(10)->withQueryString();
        $categories = Category::where('is_active', true)->get();
        $locations = Equipment::select('location')->distinct()->pluck('location');

        return view('private.equipment.index', compact('equipment', 'categories', 'locations', 'counts'));
    }

    /**
     * Muestra el formulario / modal de alta de nuevo activo institucional.
     */
    public function create(): View
    {
        $categories = Category::where('is_active', true)->get();
        return view('private.equipment.create', compact('categories'));
    }

    /**
     * Registra un nuevo activo en el inventario patrimonial.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['required', 'string', 'max:60'],
            'model' => ['required', 'string', 'max:60'],
            'serial_number' => ['required', 'string', 'max:80', 'unique:equipment,serial_number'],
            'inventory_code' => ['nullable', 'string', 'max:50', 'unique:equipment,inventory_code'],
            'specifications' => ['nullable', 'string'],
            'status' => ['nullable', 'in:disponible,prestado,en_mantenimiento,baja,reservado'],
            'physical_condition' => ['required', 'in:excelente,bueno,regular,malo'],
            'location' => ['required', 'string', 'max:100'],
            'acquisition_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ], [
            'serial_number.unique' => 'El número de serie de fabricante ya se encuentra registrado en el sistema.',
            'inventory_code.unique' => 'El código de inventario o patrimonio ya está asignado a otro equipo.',
        ]);

        // Auto-generación de código de activo institucional si se omite
        if (empty($validated['inventory_code'])) {
            $cat = Category::find($validated['category_id']);
            $prefix = $cat ? $cat->code : 'ACT';
            $validated['inventory_code'] = "ACT-{$prefix}-" . strtoupper(Str::random(5));
        }

        $validated['status'] = $validated['status'] ?? 'disponible';

        $item = Equipment::create($validated);

        AuditService::log(
            'Equipment',
            $item->id,
            'CREAR',
            "Alta de activo {$item->inventory_code} - {$item->name} (SN: {$item->serial_number})",
            $validated,
            'normal'
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Activo registrado exitosamente.',
                'equipment' => $item,
            ]);
        }

        return redirect()->route('equipment.index')->with('success', "Activo {$item->inventory_code} registrado correctamente.");
    }

    /**
     * Muestra la ficha técnica detallada y el historial operativo de un activo.
     */
    public function show(Equipment $equipment): View|JsonResponse
    {
        $equipment->load([
            'category',
            'incidents.technician',
            'incidents.logs',
            'loanItems.loan.user'
        ]);

        if (request()->wantsJson()) {
            return response()->json($equipment);
        }

        return view('private.equipment.show', compact('equipment'));
    }

    /**
     * Actualiza las especificaciones, ubicación o condición de un activo.
     */
    public function update(Request $request, Equipment $equipment): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['required', 'string', 'max:60'],
            'model' => ['required', 'string', 'max:60'],
            'serial_number' => ['required', 'string', 'max:80', Rule::unique('equipment')->ignore($equipment->id)],
            'inventory_code' => ['required', 'string', 'max:50', Rule::unique('equipment')->ignore($equipment->id)],
            'specifications' => ['nullable', 'string'],
            'status' => ['required', 'in:disponible,prestado,en_mantenimiento,baja,reservado'],
            'physical_condition' => ['required', 'in:excelente,bueno,regular,malo'],
            'location' => ['required', 'string', 'max:100'],
            'acquisition_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $oldState = $equipment->only(array_keys($validated));
        $equipment->update($validated);

        AuditService::log(
            'Equipment',
            $equipment->id,
            'ACTUALIZAR',
            "Actualización técnica de {$equipment->inventory_code} ({$equipment->name})",
            ['antes' => $oldState, 'despues' => $validated],
            'normal'
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Activo actualizado con éxito.',
                'equipment' => $equipment,
            ]);
        }

        return redirect()->route('equipment.index')->with('success', "Activo {$equipment->inventory_code} actualizado.");
    }

    /**
     * Aplica la baja lógica de un activo institucional.
     */
    public function destroy(Request $request, Equipment $equipment): RedirectResponse
    {
        $reason = $request->input('reason', 'Baja por obsolescencia o daño irreparable');

        $oldStatus = $equipment->status;
        $equipment->update([
            'status' => 'baja',
            'notes' => trim($equipment->notes . "\n[BAJA LÓGICA " . now()->toDateTimeString() . "]: " . $reason),
        ]);

        AuditService::log(
            'Equipment',
            $equipment->id,
            'BAJA',
            "Baja lógica de activo {$equipment->inventory_code} (SN: {$equipment->serial_number}). Motivo: {$reason}",
            ['estado_anterior' => $oldStatus, 'motivo' => $reason],
            'advertencia'
        );

        return redirect()->route('equipment.index')->with('success', "El activo {$equipment->inventory_code} ha sido dado de baja en el inventario.");
    }

    /**
     * Exporta el catálogo filtrado en formato CSV para reportes de auditoría.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = Equipment::with('category');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $items = $query->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="catalogo_activos_siga_' . date('Ymd_His') . '.csv"',
        ];

        return response()->stream(function () use ($items) {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8 para Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['ID Activo', 'Número de Serie', 'Nombre', 'Marca', 'Modelo', 'Categoría', 'Estatus', 'Condición Física', 'Ubicación', 'Fecha Adquisición']);

            foreach ($items as $item) {
                fputcsv($handle, [
                    $item->inventory_code,
                    $item->serial_number,
                    $item->name,
                    $item->brand,
                    $item->model,
                    $item->category->name ?? 'N/A',
                    strtoupper($item->status),
                    ucfirst($item->physical_condition),
                    $item->location,
                    $item->acquisition_date?->format('Y-m-d') ?? 'N/D',
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }
}


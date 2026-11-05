@extends('layouts.app')

@section('title', 'Control Maestro de Activos e Inventario')

@section('header')
    <div>
        <h1 class="font-headline text-xl sm:text-2xl font-bold text-primary dark:text-surface tracking-tight">
            Control Maestro de Activos e Inventario
        </h1>
        <p class="font-body text-xs text-on-surface-variant dark:text-inverse-primary mt-1">
            Gestión patrimonial, seriales, estado físico, asignaciones y bajas lógicas.
        </p>
    </div>

    <div class="flex items-center gap-2">
        <a href="{{ route('equipment.export') }}" class="neu-btn-secondary px-3.5 py-2 rounded-xl font-mono text-xs font-bold text-secondary flex items-center gap-1.5 hover:text-primary transition-all">
            <span class="material-symbols-outlined text-base">download</span>
            <span>Exportar CSV</span>
        </a>
        <button type="button" onclick="openCreateModal()" class="neu-btn-primary px-4 py-2 rounded-xl font-headline font-semibold text-xs flex items-center gap-1.5 cursor-pointer">
            <span class="material-symbols-outlined text-base">add_box</span>
            <span>+ Registrar Activo</span>
        </button>
    </div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Tarjetas de Conteo Rápido por Estatus -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <a href="{{ route('equipment.index') }}" class="neu-raised rounded-xl p-3 flex flex-col justify-between transition-transform active:scale-95 {{ !request('status') ? 'border-2 border-secondary' : '' }}">
            <span class="font-mono text-[10px] text-outline uppercase font-semibold">Total Activos</span>
            <span class="font-headline text-xl font-bold text-primary dark:text-surface mt-1">{{ $counts['total'] }}</span>
        </a>
        <a href="{{ route('equipment.index', ['status' => 'disponible']) }}" class="neu-raised rounded-xl p-3 flex flex-col justify-between transition-transform active:scale-95 {{ request('status') === 'disponible' ? 'border-2 border-emerald-500' : '' }}">
            <span class="font-mono text-[10px] text-emerald-700 dark:text-emerald-400 uppercase font-semibold flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Disponibles
            </span>
            <span class="font-headline text-xl font-bold text-emerald-700 dark:text-emerald-400 mt-1">{{ $counts['disponible'] }}</span>
        </a>
        <a href="{{ route('equipment.index', ['status' => 'prestado']) }}" class="neu-raised rounded-xl p-3 flex flex-col justify-between transition-transform active:scale-95 {{ request('status') === 'prestado' ? 'border-2 border-amber-500' : '' }}">
            <span class="font-mono text-[10px] text-amber-700 dark:text-amber-400 uppercase font-semibold flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> En Préstamo
            </span>
            <span class="font-headline text-xl font-bold text-amber-700 dark:text-amber-400 mt-1">{{ $counts['prestado'] }}</span>
        </a>
        <a href="{{ route('equipment.index', ['status' => 'en_mantenimiento']) }}" class="neu-raised rounded-xl p-3 flex flex-col justify-between transition-transform active:scale-95 {{ request('status') === 'en_mantenimiento' ? 'border-2 border-error' : '' }}">
            <span class="font-mono text-[10px] text-error uppercase font-semibold flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-error"></span> En Taller
            </span>
            <span class="font-headline text-xl font-bold text-error mt-1">{{ $counts['en_mantenimiento'] }}</span>
        </a>
        <a href="{{ route('equipment.index', ['status' => 'baja']) }}" class="neu-raised rounded-xl p-3 flex flex-col justify-between transition-transform active:scale-95 {{ request('status') === 'baja' ? 'border-2 border-outline' : '' }}">
            <span class="font-mono text-[10px] text-outline uppercase font-semibold">Bajas Lógicas</span>
            <span class="font-headline text-xl font-bold text-outline mt-1">{{ $counts['baja'] }}</span>
        </a>
    </div>

    <!-- Barra de Filtros y Búsqueda Relacional -->
    <div class="neu-raised rounded-2xl p-4">
        <form method="GET" action="{{ route('equipment.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            
            <!-- Campo Buscador -->
            <div class="sm:col-span-4 neu-inset rounded-xl flex items-center px-3 py-1.5">
                <span class="material-symbols-outlined text-secondary mr-2 text-lg">search</span>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Serie, código, marca o modelo..." 
                    class="w-full bg-transparent border-0 font-body text-xs text-on-surface focus:outline-none focus:ring-0 placeholder:text-outline p-0"
                />
            </div>

            <!-- Filtro Categoría -->
            <div class="sm:col-span-3">
                <select name="category_id" onchange="this.form.submit()" class="neu-inset w-full px-3 py-1.5 rounded-xl font-body text-xs text-on-surface border-0 focus:ring-1 focus:ring-secondary">
                    <option value="">Todas las Categorías</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }} ({{ $cat->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro Condición Física -->
            <div class="sm:col-span-3">
                <select name="physical_condition" onchange="this.form.submit()" class="neu-inset w-full px-3 py-1.5 rounded-xl font-body text-xs text-on-surface border-0 focus:ring-1 focus:ring-secondary">
                    <option value="">Cualquier Condición</option>
                    <option value="excelente" {{ request('physical_condition') === 'excelente' ? 'selected' : '' }}>Excelente</option>
                    <option value="bueno" {{ request('physical_condition') === 'bueno' ? 'selected' : '' }}>Bueno</option>
                    <option value="regular" {{ request('physical_condition') === 'regular' ? 'selected' : '' }}>Regular</option>
                    <option value="malo" {{ request('physical_condition') === 'malo' ? 'selected' : '' }}>Malo / Dañado</option>
                </select>
            </div>

            <!-- Botones de Acción -->
            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full neu-btn-primary py-1.5 px-3 rounded-xl font-headline text-xs font-semibold">
                    Filtrar
                </button>
                @if (request()->hasAny(['search', 'category_id', 'status', 'physical_condition', 'location']))
                    <a href="{{ route('equipment.index') }}" class="neu-btn-secondary p-1.5 rounded-xl text-outline hover:text-error" title="Limpiar filtros">
                        <span class="material-symbols-outlined text-base">filter_alt_off</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla Maestra de Activos -->
    <div class="neu-raised rounded-2xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse font-body text-xs">
                <thead>
                    <tr class="border-b border-outline-variant/40 bg-surface-container-low dark:bg-primary-container/40">
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Código & Serial</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Activo / Modelo</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Categoría</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Ubicación Física</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Estatus</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Condición</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($equipment as $item)
                        <tr class="hover:bg-surface-container/50 dark:hover:bg-primary/40 transition-colors">
                            <!-- Código & Serial -->
                            <td class="p-3.5">
                                <span class="font-mono font-bold text-primary dark:text-surface block">{{ $item->inventory_code }}</span>
                                <span class="font-mono text-[11px] text-outline {{ $item->status === 'baja' ? 'line-through text-error' : '' }}">
                                    SN: {{ $item->serial_number }}
                                </span>
                            </td>

                            <!-- Activo / Modelo -->
                            <td class="p-3.5">
                                <p class="font-headline font-semibold text-primary dark:text-surface">{{ $item->name }}</p>
                                <p class="font-mono text-[11px] text-outline">{{ $item->brand }} • {{ $item->model }}</p>
                            </td>

                            <!-- Categoría -->
                            <td class="p-3.5">
                                <span class="px-2.5 py-0.5 rounded-full font-mono text-[10px] bg-surface-container dark:bg-primary-container text-primary dark:text-surface font-semibold">
                                    {{ $item->category->name ?? 'General' }}
                                </span>
                            </td>

                            <!-- Ubicación -->
                            <td class="p-3.5 font-mono text-[11px] text-on-surface-variant">
                                {{ $item->location }}
                            </td>

                            <!-- Estatus -->
                            <td class="p-3.5">
                                @if ($item->status === 'disponible')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-300 font-mono text-[11px] font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-live-dot"></span>
                                        Disponible
                                    </span>
                                @elseif ($item->status === 'prestado')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-300 font-mono text-[11px] font-semibold">
                                        <span class="material-symbols-outlined text-xs">schedule</span>
                                        En Préstamo
                                    </span>
                                @elseif ($item->status === 'en_mantenimiento')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-error-container text-on-error-container border border-error/30 font-mono text-[11px] font-semibold">
                                        <span class="material-symbols-outlined text-xs">build</span>
                                        En Taller
                                    </span>
                                @elseif ($item->status === 'baja')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-700 border border-gray-300 font-mono text-[11px] font-semibold">
                                        Baja Lógica
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full bg-surface-container text-outline font-mono text-[11px]">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                @endif
                            </td>

                            <!-- Condición Física -->
                            <td class="p-3.5">
                                <span class="font-mono text-[11px] text-on-surface-variant">
                                    {{ ucfirst($item->physical_condition) }}
                                </span>
                            </td>

                            <!-- Acciones -->
                            <td class="p-3.5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <!-- Ver detalle -->
                                    <button type="button" onclick="viewAssetDetails({{ $item->id }})" class="p-1 rounded-lg hover:bg-surface-container text-on-surface-variant hover:text-primary transition-colors" title="Ver ficha técnica">
                                        <span class="material-symbols-outlined text-base">visibility</span>
                                    </button>

                                    <!-- Baja lógica (si no está ya dada de baja) -->
                                    @if ($item->status !== 'baja')
                                        <form method="POST" action="{{ route('equipment.destroy', $item) }}" onsubmit="return confirm('¿Confirma dar de baja lógica este activo? Esta acción quedará registrada en la bitácora forense.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 rounded-lg hover:bg-error-container/40 text-on-surface-variant hover:text-error transition-colors" title="Baja lógica">
                                                <span class="material-symbols-outlined text-base">delete_forever</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-outline">
                                <span class="material-symbols-outlined text-3xl mb-1 block">inventory_2</span>
                                <p class="font-headline font-semibold text-sm">No se encontraron activos con los filtros indicados.</p>
                                <p class="font-body text-xs mt-0.5">Intente con otros criterios o registre un nuevo activo.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if ($equipment->hasPages())
            <div class="p-3.5 border-t border-outline-variant/30 flex justify-between items-center">
                {{ $equipment->links() }}
            </div>
        @endif
    </div>

</div>

<!-- ========================================================================= -->
<!-- MODAL: REGISTRAR NUEVO ACTIVO                                             -->
<!-- ========================================================================= -->
<div id="modalCreateAsset" class="fixed inset-0 z-50 bg-primary/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="neu-raised rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 border border-outline-variant/40 shadow-xl">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30 mb-4">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary text-xl">inventory_2</span>
                <h3 class="font-headline text-lg font-bold text-primary dark:text-surface">Registrar Nuevo Activo Institucional</h3>
            </div>
            <button type="button" onclick="closeCreateModal()" class="text-outline hover:text-primary">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form method="POST" action="{{ route('equipment.store') }}" class="space-y-4">
            @csrf
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Categoría *</label>
                    <select name="category_id" required class="neu-inset w-full px-3 py-2 rounded-xl text-xs">
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Código de Inventario</label>
                    <input type="text" name="inventory_code" placeholder="Auto-generado si se omite" class="neu-inset w-full px-3 py-2 rounded-xl text-xs" />
                </div>
            </div>

            <div class="space-y-1">
                <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Nombre del Activo *</label>
                <input type="text" name="name" required placeholder="Ej. ThinkPad P16 Gen 2 Workstation" class="neu-inset w-full px-3 py-2 rounded-xl text-xs" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Marca *</label>
                    <input type="text" name="brand" required placeholder="Ej. Lenovo" class="neu-inset w-full px-3 py-2 rounded-xl text-xs" />
                </div>
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Modelo *</label>
                    <input type="text" name="model" required placeholder="Ej. P16v" class="neu-inset w-full px-3 py-2 rounded-xl text-xs" />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Número de Serie *</label>
                    <input type="text" name="serial_number" required placeholder="Ej. SN-8923019842" class="neu-inset w-full px-3 py-2 rounded-xl text-xs font-mono" />
                </div>
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Ubicación Física *</label>
                    <input type="text" name="location" required placeholder="Ej. Laboratorio Central A-102" class="neu-inset w-full px-3 py-2 rounded-xl text-xs" />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Condición Física *</label>
                    <select name="physical_condition" required class="neu-inset w-full px-3 py-2 rounded-xl text-xs">
                        <option value="excelente">Excelente</option>
                        <option value="bueno" selected>Bueno</option>
                        <option value="regular">Regular</option>
                        <option value="malo">Malo</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Fecha de Adquisición</label>
                    <input type="date" name="acquisition_date" value="{{ date('Y-m-d') }}" class="neu-inset w-full px-3 py-2 rounded-xl text-xs" />
                </div>
            </div>

            <div class="space-y-1">
                <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Especificaciones Técnicas</label>
                <textarea name="specifications" rows="2" placeholder="CPU, Memoria RAM, Almacenamiento, Conectividad..." class="neu-inset w-full px-3 py-2 rounded-xl text-xs"></textarea>
            </div>

            <div class="pt-3 border-t border-outline-variant/30 flex justify-end gap-2">
                <button type="button" onclick="closeCreateModal()" class="neu-btn-secondary px-4 py-2 rounded-xl font-mono text-xs font-bold text-outline">
                    Cancelar
                </button>
                <button type="submit" class="neu-btn-primary px-5 py-2 rounded-xl font-headline font-semibold text-xs flex items-center gap-1.5 cursor-pointer">
                    <span class="material-symbols-outlined text-base">check</span>
                    <span>Guardar Activo</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateModal() {
        document.getElementById('modalCreateAsset').classList.remove('hidden');
    }
    function closeCreateModal() {
        document.getElementById('modalCreateAsset').classList.add('hidden');
    }

    function viewAssetDetails(assetId) {
        fetch(`/activos/${assetId}`, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(data => {
                alert(`Ficha de Activo: ${data.inventory_code}\nNombre: ${data.name}\nSerie: ${data.serial_number}\nEstatus: ${data.status.toUpperCase()}\nUbicación: ${data.location}\nEspecificaciones: ${data.specifications || 'N/A'}`);
            })
            .catch(err => console.error(err));
    }
</script>
@endsection


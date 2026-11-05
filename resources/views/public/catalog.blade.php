@extends('layouts.public')

@section('title', 'Catálogo Institucional de Equipos y Préstamos')

@section('content')
<!-- Notificaciones Flash -->
@if (session('success'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-900 neu-raised flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-emerald-600 text-xl">check_circle</span>
                <p class="font-body text-sm font-medium">{{ session('success') }}</p>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 cursor-pointer">
                <span class="material-symbols-outlined text-base">close</span>
            </button>
        </div>
    </div>
@endif

@if (session('error'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <div class="p-4 rounded-xl bg-error-container border border-error/30 text-on-error-container neu-raised flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-error text-xl">error</span>
                <p class="font-body text-sm font-medium">{{ session('error') }}</p>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-on-error-container hover:opacity-80 cursor-pointer">
                <span class="material-symbols-outlined text-base">close</span>
            </button>
        </div>
    </div>
@endif

<!-- ========================================================================= -->
<!-- SECCIÓN HERO: BIENVENIDA Y BUSCADOR DE EQUIPOS DISPONIBLES               -->
<!-- ========================================================================= -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-10" id="hero">
    <div class="p-6 sm:p-10 rounded-3xl neu-raised border border-white/80 dark:border-primary-container relative overflow-hidden">
        
        <div class="absolute -right-16 -top-16 w-80 h-80 rounded-full bg-secondary-container/10 blur-3xl pointer-events-none"></div>

        <div class="max-w-3xl space-y-4">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full neu-inset text-xs font-mono text-secondary font-bold">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-live-dot"></span>
                <span>CATÁLOGO DE INVENTARIO VIGENTE • SISTEMA SIGA</span>
            </div>
            
            <h1 class="font-headline text-3xl sm:text-5xl font-bold text-primary dark:text-surface tracking-tight leading-tight">
                Préstamo de Activos y Equipamiento Técnico
            </h1>
            
            <p class="font-body text-sm sm:text-base text-on-surface-variant dark:text-inverse-primary leading-relaxed">
                Consulta en tiempo real la disponibilidad de laptops, equipo especializado de laboratorio, proyectores y telecomunicaciones para la comunidad universitaria.
            </p>

            <!-- Buscador Táctil -->
            <form method="GET" action="{{ route('public.catalog') }}" class="pt-2 max-w-2xl">
                <div class="neu-inset rounded-2xl p-2 flex flex-col sm:flex-row items-center gap-2">
                    <div class="flex items-center gap-2 w-full px-3 py-1">
                        <span class="material-symbols-outlined text-secondary text-2xl">search</span>
                        <input 
                            type="text" 
                            name="q" 
                            value="{{ request('q') }}" 
                            placeholder="Buscar por nombre, marca, serie o especificación..." 
                            class="w-full bg-transparent border-0 font-body text-sm text-on-surface dark:text-surface focus:outline-none focus:ring-0 placeholder:text-outline p-0"
                        />
                    </div>
                    <button type="submit" class="w-full sm:w-auto neu-btn-primary px-6 py-2.5 rounded-xl font-mono text-xs font-bold uppercase tracking-wider shrink-0 cursor-pointer">
                        Buscar
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- ========================================================================= -->
<!-- FILTROS Y CATÁLOGO DE EQUIPOS                                             -->
<!-- ========================================================================= -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4" id="catalogo">
    
    <!-- Filtros de Categorías Táctiles -->
    <div class="flex items-center justify-between flex-wrap gap-4 mb-8">
        <div>
            <h2 class="font-headline text-2xl font-bold text-primary dark:text-surface">Catálogo de Activos</h2>
            <p class="font-mono text-xs text-outline">Listado patrimonial sincronizado con el modelo relacional</p>
        </div>

        <div class="flex items-center gap-2 overflow-x-auto pb-2">
            <a href="{{ route('public.catalog') }}" class="px-4 py-2 rounded-xl font-mono text-xs font-bold transition-all {{ !request('category') ? 'neu-raised text-primary dark:text-surface font-extrabold border-2 border-secondary' : 'neu-inset text-on-surface-variant' }}">
                Todos los Equipos
            </a>
            @foreach ($categories as $cat)
                <a href="{{ route('public.catalog', ['category' => $cat->code]) }}" class="px-4 py-2 rounded-xl font-mono text-xs font-bold transition-all {{ request('category') === $cat->code ? 'neu-raised text-primary dark:text-surface font-extrabold border-2 border-secondary' : 'neu-inset text-on-surface-variant' }}">
                    {{ $cat->name }} ({{ $cat->available_equipment_count }})
                </a>
            @endforeach
        </div>
    </div>

    <!-- Rejilla de Tarjetas de Activos Dinámicas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($equipment as $item)
            @php
                $isAvailable = $item->status === 'disponible';
            @endphp
            <div class="rounded-2xl neu-raised p-6 flex flex-col justify-between neu-card-interactive border border-white/70 dark:border-primary-container">
                <div>
                    <!-- Encabezado de la Tarjeta -->
                    <div class="flex items-center justify-between mb-4">
                        @if ($isAvailable)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-mono font-bold bg-[#E6F4EA] text-[#137333] border border-[#34A853]">
                                <span class="w-2 h-2 rounded-full bg-[#137333] animate-live-dot"></span>
                                Disponible
                            </span>
                        @elseif ($item->status === 'prestado')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-mono font-bold bg-[#FFF8E1] text-[#B06000] border border-[#E09F3E]">
                                <span class="material-symbols-outlined text-sm">schedule</span>
                                En Préstamo
                            </span>
                        @elseif ($item->status === 'en_mantenimiento')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-mono font-bold bg-error-container text-on-error-container border border-error/30">
                                <span class="material-symbols-outlined text-sm">build</span>
                                En Taller
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs font-mono bg-surface-container text-outline">
                                {{ ucfirst($item->status) }}
                            </span>
                        @endif

                        <span class="font-mono text-xs text-outline font-semibold">
                            {{ $item->inventory_code }}
                        </span>
                    </div>

                    <!-- Icono según categoría -->
                    <div class="w-12 h-12 rounded-xl bg-primary-container text-secondary-container flex items-center justify-center neu-inset mb-4">
                        <span class="material-symbols-outlined text-2xl">
                            @if ($item->category && str_contains(strtolower($item->category->code), 'lap')) laptop_mac
                            @elseif ($item->category && str_contains(strtolower($item->category->code), 'proy')) videocam
                            @elseif ($item->category && str_contains(strtolower($item->category->code), 'aud')) volume_up
                            @elseif ($item->category && str_contains(strtolower($item->category->code), 'red')) router
                            @else science
                            @endif
                        </span>
                    </div>

                    <!-- Nombre & Modelo -->
                    <h3 class="font-headline text-lg font-bold text-primary dark:text-surface mb-1">
                        {{ $item->name }}
                    </h3>
                    <p class="font-mono text-[11px] text-secondary mb-2">{{ $item->brand }} • {{ $item->model }}</p>
                    
                    <p class="font-body text-xs text-on-surface-variant dark:text-inverse-primary mb-4 leading-relaxed line-clamp-2">
                        {{ $item->specifications ?: 'Equipo técnico institucional en resguardo para actividades académicas.' }}
                    </p>

                    <!-- Ficha Rápida -->
                    <div class="space-y-1.5 text-xs font-mono text-outline pb-4 border-b border-outline-variant/30">
                        <div class="flex justify-between">
                            <span>Ubicación:</span>
                            <span class="font-bold text-primary dark:text-surface">{{ $item->location }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Condición Física:</span>
                            <span class="font-bold text-secondary capitalize">{{ $item->physical_condition }}</span>
                        </div>
                    </div>
                </div>

                <!-- Footer de la Tarjeta con Acción de Solicitud -->
                <div class="pt-4 flex items-center justify-between">
                    <span class="font-mono text-[11px] text-outline">
                        SN: {{ $item->serial_number }}
                    </span>

                    @if ($isAvailable)
                        <button type="button" onclick="openPublicLoanModal({{ $item->id }}, '{{ $item->name }}', '{{ $item->inventory_code }}')" class="neu-btn-primary px-4 py-2 rounded-xl font-mono text-xs font-bold uppercase tracking-wider cursor-pointer">
                            Solicitar Préstamo
                        </button>
                    @else
                        <button class="neu-btn-secondary px-4 py-2 rounded-xl font-mono text-xs font-bold uppercase tracking-wider opacity-60 cursor-not-allowed" disabled>
                            No Disponible
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full p-12 text-center text-outline neu-raised rounded-2xl">
                <span class="material-symbols-outlined text-4xl mb-2 block">devices</span>
                <p class="font-headline font-semibold text-base text-primary dark:text-surface">No se encontraron equipos en esta categoría.</p>
                <p class="font-body text-xs mt-1">Modifique los términos de búsqueda o seleccione otra categoría.</p>
            </div>
        @endforelse
    </div>

    <!-- Paginación -->
    @if ($equipment->hasPages())
        <div class="pt-8 flex justify-center">
            {{ $equipment->links() }}
        </div>
    @endif
</section>

<!-- ========================================================================= -->
<!-- SECCIÓN DE INFORMACIÓN: CÓMO FUNCIONA EL PRÉSTAMO                         -->
<!-- ========================================================================= -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16" id="como-funciona">
    <div class="p-8 sm:p-12 rounded-3xl neu-inset">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="font-mono text-xs text-secondary uppercase tracking-widest font-bold">Procedimiento Institucional</span>
            <h2 class="font-headline text-2xl sm:text-3xl font-bold text-primary dark:text-surface mt-1">¿Cómo solicitar un equipo?</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="p-6 rounded-2xl neu-raised text-center space-y-3">
                <div class="w-12 h-12 mx-auto rounded-xl bg-primary-container text-secondary-container flex items-center justify-center font-headline font-bold text-lg">
                    1
                </div>
                <h3 class="font-headline text-base font-bold text-primary dark:text-surface">Consulta & Solicitud</h3>
                <p class="font-body text-xs text-on-surface-variant dark:text-inverse-primary leading-relaxed">
                    Localiza el equipo en el catálogo y genera tu folio digital de solicitud con tu matrícula o número de empleado institucional.
                </p>
            </div>

            <div class="p-6 rounded-2xl neu-raised text-center space-y-3">
                <div class="w-12 h-12 mx-auto rounded-xl bg-primary-container text-secondary-container flex items-center justify-center font-headline font-bold text-lg">
                    2
                </div>
                <h3 class="font-headline text-base font-bold text-primary dark:text-surface">Recepción en Ventanilla</h3>
                <p class="font-body text-xs text-on-surface-variant dark:text-inverse-primary leading-relaxed">
                    Presenta tu credencial universitaria en el módulo central de préstamos para validación de accesorios y entrega del bien.
                </p>
            </div>

            <div class="p-6 rounded-2xl neu-raised text-center space-y-3">
                <div class="w-12 h-12 mx-auto rounded-xl bg-primary-container text-secondary-container flex items-center justify-center font-headline font-bold text-lg">
                    3
                </div>
                <h3 class="font-headline text-base font-bold text-primary dark:text-surface">Devolución y Checklist</h3>
                <p class="font-body text-xs text-on-surface-variant dark:text-inverse-primary leading-relaxed">
                    Entrega el equipo en la fecha pactada. El técnico validará el estado funcional y emitirá tu liberación de resguardo.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- ========================================================================= -->
<!-- MODAL: SOLICITUD DE PRÉSTAMO PÚBLICA                                      -->
<!-- ========================================================================= -->
<div id="modalPublicLoan" class="fixed inset-0 z-50 bg-primary/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="neu-raised rounded-2xl w-full max-w-lg p-6 border border-outline-variant/40 shadow-xl">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30 mb-4">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary text-xl">handshake</span>
                <h3 class="font-headline text-lg font-bold text-primary dark:text-surface">
                    Solicitar Préstamo de Activo
                </h3>
            </div>
            <button type="button" onclick="closePublicLoanModal()" class="text-outline hover:text-primary">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form method="POST" action="{{ route('public.catalog.request') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="equipment_id" id="publicLoanEquipId" value="" />

            <div class="p-3 rounded-xl neu-inset bg-surface-container-low">
                <span class="font-mono text-[10px] text-outline uppercase font-bold block">Equipo Seleccionado:</span>
                <p class="font-headline font-bold text-sm text-primary dark:text-surface" id="publicLoanEquipName"></p>
                <span class="font-mono text-xs text-secondary" id="publicLoanEquipCode"></span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary">Matrícula o Clave *</label>
                    <input type="text" name="institutional_id" required placeholder="Ej. ALU-20240189" class="neu-inset w-full px-3 py-2 rounded-xl text-xs font-mono" />
                </div>
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary">Correo Institucional *</label>
                    <input type="email" name="email" required placeholder="ejemplo@universidad.edu.mx" class="neu-inset w-full px-3 py-2 rounded-xl text-xs" />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary">Lugar de Uso *</label>
                    <input type="text" name="location_of_use" required placeholder="Ej. Aula 12 Edificio B" class="neu-inset w-full px-3 py-2 rounded-xl text-xs" />
                </div>
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary">Fecha de Retorno *</label>
                    <input type="datetime-local" name="expected_return_date" required value="{{ now()->addDays(2)->format('Y-m-d\TH:i') }}" class="neu-inset w-full px-3 py-2 rounded-xl text-xs" />
                </div>
            </div>

            <div class="space-y-1">
                <label class="block font-mono text-xs font-bold text-primary">Propósito o Motivo Académico *</label>
                <textarea name="purpose" required rows="2" placeholder="Práctica de laboratorio, presentación, proyecto terminal..." class="neu-inset w-full px-3 py-2 rounded-xl text-xs"></textarea>
            </div>

            <div class="pt-3 border-t border-outline-variant/30 flex justify-end gap-2">
                <button type="button" onclick="closePublicLoanModal()" class="neu-btn-secondary px-4 py-2 rounded-xl font-mono text-xs font-bold text-outline">
                    Cancelar
                </button>
                <button type="submit" class="neu-btn-primary px-5 py-2 rounded-xl font-headline font-semibold text-xs flex items-center gap-1.5 cursor-pointer">
                    <span class="material-symbols-outlined text-base">send</span>
                    <span>Generar Folio de Solicitud</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openPublicLoanModal(id, name, code) {
        document.getElementById('publicLoanEquipId').value = id;
        document.getElementById('publicLoanEquipName').textContent = name;
        document.getElementById('publicLoanEquipCode').textContent = `Código: ${code}`;
        document.getElementById('modalPublicLoan').classList.remove('hidden');
    }
    function closePublicLoanModal() {
        document.getElementById('modalPublicLoan').classList.add('hidden');
    }
</script>
@endsection

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

@if ($errors->any())
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <div class="p-4 rounded-xl bg-error-container border border-error/30 text-on-error-container neu-raised shadow-xs">
            <div class="flex items-center justify-between gap-2 font-bold mb-1">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-error text-xl">warning</span>
                    <span class="font-body text-sm">Por favor verifique los siguientes datos:</span>
                </div>
                <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-on-error-container hover:opacity-80 cursor-pointer">
                    <span class="material-symbols-outlined text-base">close</span>
                </button>
            </div>
            <ul class="list-disc list-inside text-xs font-body space-y-1 ml-6">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<section class="mx-auto max-w-7xl px-4 pb-8 pt-8 sm:px-6 lg:px-8" id="hero">
    <div class="overflow-hidden rounded-3xl bg-primary-container px-6 py-8 text-white shadow-lg sm:px-10 sm:py-10">
        <div class="max-w-3xl">
            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 font-mono text-[10px] font-bold uppercase tracking-wider text-secondary-container">
                <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                Catálogo institucional · {{ $availableEquipmentCount }} equipos disponibles
            </span>
            <h1 class="mt-4 font-headline text-3xl font-bold tracking-tight sm:text-5xl">Encuentra el equipo que necesitas</h1>
            <p class="mt-3 max-w-2xl text-sm leading-relaxed text-white/75 sm:text-base">
                Explora el inventario disponible por categoría y solicita un préstamo desde tu cuenta institucional aprobada.
            </p>

            {{-- ============================================================ --}}
            {{-- BARRA DE BÚSQUEDA CON SUGERENCIAS (sólo aplica con el botón) --}}
            {{-- ============================================================ --}}
            <form method="GET" action="{{ route('public.catalog') }}" id="catalogSearchForm" class="mt-6 max-w-2xl relative">
                @if(request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                <div class="relative flex flex-col gap-2 rounded-2xl bg-white p-2 shadow-lg sm:flex-row">
                    <label class="flex min-w-0 flex-1 items-center gap-2 px-3">
                        <span class="material-symbols-outlined text-outline">search</span>
                        <input
                            type="text"
                            name="q"
                            id="catalogSearchInput"
                            value="{{ request('q') }}"
                            placeholder="Buscar por nombre, marca o tipo de equipo…"
                            aria-label="Buscar equipos disponibles"
                            autocomplete="off"
                            class="w-full border-0 bg-transparent text-sm text-primary placeholder:text-outline focus:ring-0"
                        />
                        {{-- Botón limpiar búsqueda --}}
                        <button type="button" id="clearSearchBtn"
                            onclick="clearSearchInput()"
                            class="{{ request('q') ? '' : 'hidden' }} text-outline hover:text-primary p-1 rounded-lg transition"
                            aria-label="Limpiar búsqueda"
                            title="Limpiar búsqueda">
                            <span class="material-symbols-outlined text-base">close</span>
                        </button>
                    </label>
                    <button type="submit" id="btnSubmitSearch" class="rounded-xl bg-secondary-container px-6 py-3 font-headline text-sm font-bold text-primary transition hover:brightness-95 flex items-center justify-center gap-2 cursor-pointer">
                        <span class="material-symbols-outlined text-base">search</span>
                        <span>Buscar equipos</span>
                    </button>
                </div>

                {{-- Menú desplegable de sugerencias y semejanzas en tiempo real --}}
                <div id="searchSuggestions"
                     class="hidden absolute z-50 left-0 right-0 top-full mt-2 rounded-2xl bg-white shadow-2xl border border-outline-variant/40 overflow-hidden divide-y divide-outline-variant/20 max-h-80 overflow-y-auto">
                </div>
            </form>
        </div>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 pb-12 sm:px-6 lg:px-8" id="catalogo">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[250px_minmax(0,1fr)]">

        {{-- ============================================================ --}}
        {{-- PANEL LATERAL DE CATEGORÍAS (nombres amigables y claros)     --}}
        {{-- ============================================================ --}}
        <aside aria-label="Categorías de equipos" class="h-fit rounded-2xl border border-outline-variant/50 bg-white p-4 shadow-sm dark:bg-primary-container">
            <div class="mb-3 flex items-center gap-2 border-b border-outline-variant/40 pb-3">
                <span class="material-symbols-outlined text-secondary">category</span>
                <h2 class="font-headline text-base font-bold text-primary dark:text-surface">Tipo de equipo</h2>
            </div>
            @php
                $catIcons = [
                    'LAP'  => 'laptop_mac',
                    'PROY' => 'videocam',
                    'AUD'  => 'volume_up',
                    'RED'  => 'router',
                    'PER'  => 'keyboard',
                ];
                $friendlyNames = [
                    'LAP'  => 'Laptops y Computadoras',
                    'PROY' => 'Proyectores y Pantallas',
                    'AUD'  => 'Bocinas y Micrófonos',
                    'RED'  => 'Redes e Internet',
                    'PER'  => 'Accesorios y Cables',
                ];
            @endphp
            <nav class="space-y-1">
                <a
                    href="{{ route('public.catalog', array_filter(['q' => request('q')])) }}"
                    @if (!request('category')) aria-current="page" @endif
                    class="flex items-center justify-between rounded-xl px-3 py-2.5 text-sm transition {{ !request('category') ? 'bg-primary-container font-bold text-white' : 'text-on-surface-variant hover:bg-surface-container-low dark:text-inverse-primary dark:hover:bg-primary/60' }}"
                >
                    <span class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">apps</span>
                        Todos los equipos
                    </span>
                    <span class="font-mono text-xs">{{ $availableEquipmentCount }}</span>
                </a>
                @foreach ($categories as $cat)
                    @php
                        $cName = $friendlyNames[$cat->code] ?? $cat->name;
                    @endphp
                    <a
                        href="{{ route('public.catalog', array_filter(['category' => $cat->code, 'q' => request('q')])) }}"
                        @if (request('category') === $cat->code) aria-current="page" @endif
                        class="flex items-center justify-between gap-2 rounded-xl px-3 py-2.5 text-sm transition {{ request('category') === $cat->code ? 'bg-primary-container font-bold text-white' : 'text-on-surface-variant hover:bg-surface-container-low dark:text-inverse-primary dark:hover:bg-primary/60' }}"
                    >
                        <span class="flex items-center gap-2 min-w-0">
                            <span class="material-symbols-outlined text-lg shrink-0">{{ $catIcons[$cat->code] ?? 'devices' }}</span>
                            <span class="min-w-0 truncate">{{ $cName }}</span>
                        </span>
                        <span class="shrink-0 rounded-full bg-surface-container-low px-2 py-0.5 font-mono text-[10px] text-primary dark:bg-primary dark:text-surface">{{ $cat->available_equipment_count }}</span>
                    </a>
                @endforeach
            </nav>
            <div class="mt-4 rounded-xl bg-surface-container-low p-3 dark:bg-primary">
                <p class="font-mono text-[10px] font-bold uppercase tracking-wider text-secondary">¿Aún no tienes cuenta?</p>
                <p class="mt-1 text-xs text-on-surface-variant dark:text-inverse-primary">Regístrate y espera la aprobación del personal institucional.</p>
                <a href="{{ route('register') }}" class="mt-3 inline-flex items-center gap-1 text-xs font-bold text-primary hover:underline dark:text-secondary-container">
                    Crear cuenta <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>
        </aside>

        {{-- ============================================================ --}}
        {{-- LISTADO DE PRODUCTOS / EQUIPOS                               --}}
        {{-- ============================================================ --}}
        <div class="min-w-0">
            <div class="mb-4 flex flex-wrap items-end justify-between gap-2">
                <div>
                    <p class="font-mono text-[10px] font-bold uppercase tracking-widest text-secondary">Inventario disponible para préstamo</p>
                    <h2 class="font-headline text-2xl font-bold text-primary dark:text-surface">
                        @if(request('category'))
                            {{ $friendlyNames[request('category')] ?? ($categories->firstWhere('code', request('category'))?->name ?? 'Equipos disponibles') }}
                        @else
                            Todos los equipos disponibles
                        @endif
                    </h2>
                    @if(request('q'))
                        <div class="mt-2 flex items-center gap-2 flex-wrap">
                            <span class="text-xs text-on-surface-variant">Búsqueda activa:</span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-secondary-container/20 border border-secondary text-primary font-mono text-xs font-semibold">
                                "{{ request('q') }}"
                                <a href="{{ route('public.catalog', array_filter(['category' => request('category')])) }}"
                                   class="text-primary hover:text-error ml-1" title="Eliminar filtro de búsqueda">
                                    <span class="material-symbols-outlined text-sm">cancel</span>
                                </a>
                            </span>
                            <a href="{{ route('public.catalog') }}" class="text-xs text-secondary hover:underline font-medium ml-2">
                                Restablecer todo
                            </a>
                        </div>
                    @endif
                </div>
                <p class="font-body text-xs text-outline">{{ $equipment->total() }} {{ $equipment->total() === 1 ? 'resultado' : 'resultados' }}</p>
            </div>

            @php
                $catPlaceholderBg = [
                    'LAP'  => 'bg-blue-50',
                    'PROY' => 'bg-amber-50',
                    'AUD'  => 'bg-purple-50',
                    'RED'  => 'bg-teal-50',
                    'PER'  => 'bg-rose-50',
                ];
                $catPlaceholderIcon = [
                    'LAP'  => ['icon' => 'laptop_mac',  'color' => 'text-blue-500'],
                    'PROY' => ['icon' => 'videocam',     'color' => 'text-amber-500'],
                    'AUD'  => ['icon' => 'volume_up',    'color' => 'text-purple-500'],
                    'RED'  => ['icon' => 'router',       'color' => 'text-teal-500'],
                    'PER'  => ['icon' => 'keyboard',     'color' => 'text-rose-500'],
                ];
                $conditionLabels = [
                    'excelente' => ['label' => 'Excelente estado',  'class' => 'bg-emerald-100 text-emerald-800 border-emerald-300'],
                    'bueno'     => ['label' => 'Buen estado',       'class' => 'bg-blue-100 text-blue-800 border-blue-300'],
                    'regular'   => ['label' => 'Estado regular',    'class' => 'bg-amber-100 text-amber-800 border-amber-300'],
                    'malo'      => ['label' => 'Requiere revisión', 'class' => 'bg-red-100 text-red-800 border-red-300'],
                ];
            @endphp

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @forelse ($equipment as $item)
                    @php
                        $code = $item->category?->code ?? '';
                        $placeholderBg   = $catPlaceholderBg[$code] ?? 'bg-surface-container-low';
                        $placeholderMeta = $catPlaceholderIcon[$code] ?? ['icon' => 'devices', 'color' => 'text-primary/50'];
                        $condInfo        = $conditionLabels[$item->physical_condition] ?? ['label' => ucfirst($item->physical_condition), 'class' => 'bg-gray-100 text-gray-700 border-gray-300'];
                        $friendlyCategory = $friendlyNames[$code] ?? ($item->category->name ?? 'Equipo institucional');
                    @endphp
                    <article class="group flex min-w-0 flex-col overflow-hidden rounded-2xl border border-outline-variant/50 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl dark:bg-primary-container">

                        {{-- Imagen del producto --}}
                        <div class="relative flex h-52 items-center justify-center {{ $placeholderBg }} dark:bg-primary/70 overflow-hidden border-b border-outline-variant/30">
                            @if (!empty($item->image_url))
                                <img
                                    src="{{ $item->image_url }}"
                                    alt="Fotografía de {{ $item->name }}"
                                    class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                    loading="lazy"
                                    onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');"
                                />
                                <div class="hidden h-full w-full flex-col items-center justify-center p-4 text-center">
                                    <span class="material-symbols-outlined text-6xl {{ $placeholderMeta['color'] }}">{{ $placeholderMeta['icon'] }}</span>
                                    <span class="text-[11px] text-outline mt-1 font-body">Imagen no disponible</span>
                                </div>
                            @else
                                <div class="h-full w-full flex flex-col items-center justify-center p-4 text-center">
                                    <span class="material-symbols-outlined text-6xl {{ $placeholderMeta['color'] }}">{{ $placeholderMeta['icon'] }}</span>
                                    <span class="text-[11px] text-outline mt-1 font-body">Foto de referencia</span>
                                </div>
                            @endif

                            {{-- Estado de disponibilidad --}}
                            <span class="absolute left-3 top-3 inline-flex items-center gap-1 rounded-full bg-emerald-500/95 text-white px-2.5 py-1 font-mono text-[10px] font-bold uppercase shadow-md backdrop-blur-xs">
                                <span class="h-1.5 w-1.5 rounded-full bg-white animate-pulse"></span> Disponible
                            </span>
                            {{-- Código institucional --}}
                            <span class="absolute right-3 top-3 rounded-full bg-white/95 px-2.5 py-1 font-mono text-[10px] font-bold text-primary shadow-md border border-outline-variant/30">
                                {{ $item->inventory_code }}
                            </span>
                        </div>

                        {{-- Información del producto --}}
                        <div class="flex flex-1 flex-col p-5">
                            {{-- Categoría amigable --}}
                            <span class="font-mono text-[10px] font-semibold uppercase tracking-wider text-secondary">{{ $friendlyCategory }}</span>

                            {{-- Nombre del equipo --}}
                            <h3 class="mt-1 font-headline text-base font-bold leading-snug text-primary dark:text-surface group-hover:text-secondary transition-colors">
                                {{ $item->name }}
                            </h3>

                            {{-- Marca y Modelo --}}
                            <div class="mt-1 flex items-center gap-1.5 font-body text-xs text-on-surface-variant dark:text-inverse-primary">
                                <span class="material-symbols-outlined text-sm text-outline">sell</span>
                                <span class="font-medium text-primary dark:text-surface">{{ $item->brand }}</span>
                                <span class="text-outline">·</span>
                                <span>{{ $item->model }}</span>
                            </div>

                            {{-- Especificaciones / Detalles del equipo --}}
                            <p class="mt-3 line-clamp-3 flex-1 text-xs leading-relaxed text-on-surface-variant dark:text-inverse-primary">
                                {{ $item->specifications ?: 'Equipo disponible para actividades académicas, prácticas de laboratorio y proyectos institucionales.' }}
                            </p>

                            {{-- Datos lógicos: Ubicación y Estado físico --}}
                            <div class="mt-4 space-y-2 border-t border-outline-variant/40 pt-3">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-outline flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">location_on</span>
                                        Lugar de entrega:
                                    </span>
                                    <span class="font-medium text-primary dark:text-surface truncate max-w-[150px]">{{ $item->location }}</span>
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-outline flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">verified</span>
                                        Estado físico:
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $condInfo['class'] }}">
                                        {{ $condInfo['label'] }}
                                    </span>
                                </div>
                            </div>

                            {{-- Botón de acción --}}
                            <div class="mt-5">
                                @auth
                                    @if (Auth::user()->isUsuario())
                                        <button type="button"
                                            onclick="openPublicLoanModal({{ $item->id }}, @js($item->name), @js($item->inventory_code))"
                                            class="w-full rounded-xl bg-primary-container px-4 py-2.5 font-mono text-xs font-bold uppercase tracking-wide text-white transition hover:bg-primary flex items-center justify-center gap-2 shadow-xs cursor-pointer">
                                            <span class="material-symbols-outlined text-base">handshake</span>
                                            <span>Solicitar préstamo</span>
                                        </button>
                                    @else
                                        <p class="rounded-xl bg-surface-container-low px-3 py-2.5 text-center text-xs text-outline dark:bg-primary">Inicia sesión con una cuenta solicitante para pedirlo.</p>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" class="block w-full rounded-xl bg-primary-container px-4 py-2.5 text-center font-mono text-xs font-bold uppercase tracking-wide text-white transition hover:bg-primary flex items-center justify-center gap-2 shadow-xs">
                                        <span class="material-symbols-outlined text-base">lock_open</span>
                                        <span>Iniciar sesión para solicitar</span>
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full rounded-3xl border border-outline-variant/50 bg-white p-10 text-center shadow-sm dark:bg-primary-container">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-surface-container-low text-secondary mb-4">
                            <span class="material-symbols-outlined text-4xl">search_off</span>
                        </div>
                        <h3 class="font-headline text-lg font-bold text-primary dark:text-surface">No se encontraron equipos con los filtros seleccionados</h3>
                        <p class="mt-1 text-sm text-on-surface-variant dark:text-inverse-primary max-w-md mx-auto">
                            No hay resultados para la búsqueda actual. Puedes intentar con otras palabras o restablecer los filtros para ver todo el inventario disponible.
                        </p>
                        <div class="mt-5 flex items-center justify-center gap-3">
                            <a href="{{ route('public.catalog') }}" class="inline-flex items-center gap-2 rounded-xl bg-primary-container px-5 py-2.5 font-mono text-xs font-bold uppercase text-white transition hover:bg-primary shadow-xs">
                                <span class="material-symbols-outlined text-base">restart_alt</span>
                                <span>Ver todos los equipos</span>
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>

            @if ($equipment->hasPages())
                <div class="pt-8">{{ $equipment->links() }}</div>
            @endif
        </div>
    </div>
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
<!-- HORARIOS DE ATENCIÓN Y ENTREGA                                            -->
<!-- ========================================================================= -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16" id="horarios">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        <div>
            <span class="font-mono text-xs text-secondary uppercase tracking-widest font-bold">Ventanilla y bodega central</span>
            <h2 class="font-headline text-2xl sm:text-3xl font-bold text-primary dark:text-surface mt-1 mb-4">Horarios de Atención y Entrega de Equipamiento</h2>
            <p class="font-body text-sm text-on-surface-variant dark:text-inverse-primary leading-relaxed mb-6">Las entregas y devoluciones se realizan en los bloques autorizados para revisar la condición y los accesorios de cada activo.</p>
            <div class="space-y-3">
                <div class="neu-raised rounded-xl p-4 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3"><span class="material-symbols-outlined text-secondary">wb_sunny</span><div><h3 class="font-headline text-sm font-bold text-primary dark:text-surface">Turno Matutino</h3><p class="font-body text-xs text-outline">Lunes a viernes · Despacho principal</p></div></div>
                    <span class="font-mono text-xs font-bold text-secondary">07:30 - 13:00 hrs</span>
                </div>
                <div class="neu-raised rounded-xl p-4 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3"><span class="material-symbols-outlined text-secondary">wb_twilight</span><div><h3 class="font-headline text-sm font-bold text-primary dark:text-surface">Turno Vespertino</h3><p class="font-body text-xs text-outline">Lunes a viernes · Devoluciones y cierre</p></div></div>
                    <span class="font-mono text-xs font-bold text-secondary">14:30 - 18:30 hrs</span>
                </div>
                <div class="neu-raised rounded-xl p-4 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3"><span class="material-symbols-outlined text-secondary">notification_important</span><div><h3 class="font-headline text-sm font-bold text-primary dark:text-surface">Guardia Sabatina</h3><p class="font-body text-xs text-outline">Sábados · Solo urgencias TI con ticket aprobado</p></div></div>
                    <span class="font-mono text-xs font-bold text-secondary">09:00 - 13:00 hrs</span>
                </div>
            </div>
            <p class="mt-4 font-body text-xs text-on-surface-variant dark:text-inverse-primary"><strong>Ubicación:</strong> Edificio Central Corporativo, Planta Baja, Módulo C-12.</p>
        </div>

        <div class="neu-inset rounded-2xl p-6 sm:p-8">
            <span class="font-mono text-xs text-secondary uppercase tracking-widest font-bold">Política de préstamo</span>
            <h2 class="font-headline text-xl sm:text-2xl font-bold text-primary dark:text-surface mt-1 mb-6">Reglas y Políticas de Custodia de Equipos</h2>
            <ol class="space-y-5">
                <li class="flex gap-3"><span class="font-mono text-xs font-bold text-secondary">01</span><div><h3 class="font-headline text-sm font-bold text-primary dark:text-surface">Identificación obligatoria</h3><p class="font-body text-xs text-on-surface-variant dark:text-inverse-primary">La entrega requiere credencial institucional y firma de recepción. No se entregan activos a terceros sin autorización.</p></div></li>
                <li class="flex gap-3"><span class="font-mono text-xs font-bold text-secondary">02</span><div><h3 class="font-headline text-sm font-bold text-primary dark:text-surface">Devolución en fecha</h3><p class="font-body text-xs text-on-surface-variant dark:text-inverse-primary">Laptops y proyectores: hasta 15 días, con una posible prórroga de 5 días. Las herramientas se devuelven al finalizar la jornada o el proyecto.</p></div></li>
                <li class="flex gap-3"><span class="font-mono text-xs font-bold text-secondary">03</span><div><h3 class="font-headline text-sm font-bold text-primary dark:text-surface">Inspección de retorno</h3><p class="font-body text-xs text-on-surface-variant dark:text-inverse-primary">Al devolver el activo se revisan equipo, pantalla, accesorios y cables. Reporta cualquier falla mediante un ticket.</p></div></li>
                <li class="flex gap-3"><span class="font-mono text-xs font-bold text-secondary">04</span><div><h3 class="font-headline text-sm font-bold text-primary dark:text-surface">Responsabilidad de resguardo</h3><p class="font-body text-xs text-on-surface-variant dark:text-inverse-primary">La persona solicitante es responsable del cuidado del bien durante el periodo del préstamo.</p></div></li>
            </ol>
        </div>
    </div>
</section>

<!-- ========================================================================= -->
<!-- REGLAMENTO DE RESGUARDO Y PRIVACIDAD                                      -->
<!-- ========================================================================= -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16" id="terminos">
    <div class="neu-raised rounded-2xl p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-5">
        <div>
            <span class="font-mono text-xs text-secondary uppercase tracking-widest font-bold">Reglamento institucional</span>
            <h2 class="font-headline text-xl sm:text-2xl font-bold text-primary dark:text-surface mt-1">Reglas de resguardo y uso responsable</h2>
            <p class="font-body text-sm text-on-surface-variant dark:text-inverse-primary mt-2 max-w-2xl">Consulta las reglas de custodia aquí y abre los términos completos de privacidad y responsabilidad cuando necesites revisarlos.</p>
        </div>
        <button type="button" data-open-terms class="neu-btn-primary shrink-0 inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-mono text-xs font-bold uppercase tracking-wider cursor-pointer">
            <span class="material-symbols-outlined text-lg">description</span>
            <span>Ver términos</span>
        </button>
    </div>
</section>

<!-- Modal de términos completos -->
<div id="terms-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-primary/70 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="terms-modal-title">
    <div class="neu-raised neu-floating flex max-h-[85vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl">
        <div class="flex items-start justify-between gap-4 border-b border-outline-variant/40 p-5 sm:p-6">
            <div>
                <span class="font-mono text-xs font-bold uppercase tracking-widest text-secondary">Reglamento institucional</span>
                <h2 id="terms-modal-title" class="mt-1 font-headline text-xl font-bold text-primary dark:text-surface">Términos de Resguardo de Activos y Privacidad</h2>
            </div>
            <button type="button" data-close-terms aria-label="Cerrar términos" class="rounded-lg p-2 text-outline hover:bg-surface-container hover:text-primary dark:hover:bg-primary-container cursor-pointer">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="space-y-6 overflow-y-auto p-5 font-body text-sm leading-relaxed text-on-surface-variant dark:text-inverse-primary sm:p-6">
            <div><h3 class="mb-1 font-headline font-bold text-primary dark:text-surface">1. Objeto y Propiedad Institucional</h3><p>Todos los dispositivos electrónicos, equipos de cómputo portátil, periféricos, herramientas técnicas y accesorios disponibles en SIGA Enterprise son propiedad exclusiva de la empresa. El préstamo concede una custodia temporal estrictamente para actividades laborales inherentes al cargo del empleado.</p></div>
            <div><h3 class="mb-1 font-headline font-bold text-primary dark:text-surface">2. Custodia, Cuidado y Pérdida</h3><p>El usuario solicitante es el único responsable legal y administrativo del resguardo físico del activo desde su retiro de ventanilla hasta su recepción formal en almacén. En caso de robo con violencia o extravío fuera de las instalaciones, se deberá tramitar de inmediato el acta judicial correspondiente y notificar a la mesa de control dentro de las primeras 12 horas.</p></div>
            <div><h3 class="mb-1 font-headline font-bold text-primary dark:text-surface">3. Seguridad Informática y Datos Corporativos</h3><p>Queda prohibida la instalación de software no homologado, la desactivación de agentes de telemetría y antivirus, así como la transferencia de datos confidenciales a dispositivos de almacenamiento externos no encriptados. El área de Seguridad Informática se reserva el derecho de borrado remoto preventivo en casos de alerta de extravío.</p></div>
            <div><h3 class="mb-1 font-headline font-bold text-primary dark:text-surface">4. Retorno y Sanciones por Incumplimiento</h3><p>La falta de devolución en la fecha estipulada derivará en el bloqueo de solicitudes posteriores y reporte automático a la Gerencia de Recursos Humanos y Auditoría Interna. Los daños ocasionados por dolo o negligencia comprobable podrán ser sujetos a deducción conforme a la legislación laboral aplicable.</p></div>
            <div><h3 class="mb-1 font-headline font-bold text-primary dark:text-surface">5. Privacidad y Protección de Datos Personales</h3><p>Los datos recabados en los formularios (número de empleado, departamento, firmas biométricas) se almacenan bajo rigurosos protocolos de cifrado y se emplean exclusivamente para la trazabilidad operativa y el control patrimonial de la institución.</p></div>
        </div>
        <div class="flex justify-end border-t border-outline-variant/40 p-5 sm:p-6">
            <button type="button" data-close-terms class="neu-btn-primary inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 font-mono text-xs font-bold uppercase tracking-wider cursor-pointer">
                <span class="material-symbols-outlined text-lg">check</span>
                <span>Aceptar términos</span>
            </button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: SOLICITUD DE PRÉSTAMO PÚBLICA                                      -->
<!-- ========================================================================= -->
@auth
<div id="modalPublicLoan" class="fixed inset-0 z-50 bg-primary/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="neu-raised rounded-2xl w-full max-w-lg p-6 border border-outline-variant/40 shadow-xl">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30 mb-4">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary text-xl">handshake</span>
                <h3 class="font-headline text-lg font-bold text-primary dark:text-surface">
                    Solicitar Préstamo de Activo
                </h3>
            </div>
            <button type="button" onclick="closePublicLoanModal()" class="text-outline hover:text-primary cursor-pointer">
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

            <div class="rounded-xl bg-surface-container-low p-3 dark:bg-primary">
                <span class="font-mono text-[10px] font-bold uppercase text-outline">Solicitante:</span>
                <p class="mt-1 font-headline text-sm font-bold text-primary dark:text-surface">{{ Auth::user()->name }}</p>
                <p class="font-body text-xs text-on-surface-variant dark:text-inverse-primary">La solicitud se vinculará a tu cuenta institucional aprobada.</p>
            </div>

            <div class="grid grid-cols-1 gap-3">
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Lugar donde se usará *</label>
                    <input type="text" name="location_of_use" value="{{ old('location_of_use') }}" required placeholder="Ej. Aula 12, Laboratorio B..." class="neu-inset w-full px-3 py-2 rounded-xl text-xs" />
                </div>
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Fecha y hora pactada de devolución *</label>
                    <x-schedule-picker
                        name="expected_return_date"
                        mode="datetime"
                        :required="true"
                        :value="old('expected_return_date', \App\Support\OperatingSchedule::defaultReturnDateTime()->format('Y-m-d\TH:i'))"
                    />
                </div>
            </div>

            <div class="space-y-1">
                <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Motivo de la solicitud *</label>
                <textarea name="purpose" required rows="2" placeholder="Práctica de laboratorio, presentación, proyecto terminal..." class="neu-inset w-full px-3 py-2 rounded-xl text-xs"></textarea>
            </div>

            <div class="pt-3 border-t border-outline-variant/30 flex justify-end gap-2">
                <button type="button" onclick="closePublicLoanModal()" class="neu-btn-secondary px-4 py-2 rounded-xl font-mono text-xs font-bold text-outline cursor-pointer">
                    Cancelar
                </button>
                <button type="submit" class="neu-btn-primary px-5 py-2 rounded-xl font-headline font-semibold text-xs flex items-center gap-1.5 cursor-pointer">
                    <span class="material-symbols-outlined text-base">send</span>
                    <span>Enviar Solicitud de Préstamo</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endauth

<script>
    // Términos y condiciones
    const termsModal = document.getElementById('terms-modal');
    const termsClosers = document.querySelectorAll('[data-close-terms]');

    function openTermsModal() {
        if (!termsModal) return;
        termsModal.classList.remove('hidden');
        termsModal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        termsClosers[0]?.focus();
    }

    function closeTermsModal() {
        if (!termsModal) return;
        termsModal.classList.add('hidden');
        termsModal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    document.addEventListener('click', (event) => {
        if (event.target instanceof Element && event.target.closest('[data-open-terms]')) openTermsModal();
    });
    termsClosers.forEach((button) => button.addEventListener('click', closeTermsModal));
    if (termsModal) {
        termsModal.addEventListener('click', (event) => {
            if (event.target === termsModal) closeTermsModal();
        });
    }
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && termsModal && !termsModal.classList.contains('hidden')) closeTermsModal();
    });

    // Modal de solicitud de préstamo
    function openPublicLoanModal(id, name, code) {
        document.getElementById('publicLoanEquipId').value = id;
        document.getElementById('publicLoanEquipName').textContent = name;
        document.getElementById('publicLoanEquipCode').textContent = `Código: ${code}`;
        document.getElementById('modalPublicLoan').classList.remove('hidden');
        if (window.sigaInitSchedulePickers) window.sigaInitSchedulePickers();
    }
    function closePublicLoanModal() {
        document.getElementById('modalPublicLoan').classList.add('hidden');
    }

    // =========================================================================
    // LÓGICA DE BÚSQUEDA Y SUGERENCIAS EN TIEMPO REAL (con semejanzas)
    // =========================================================================
    const searchInput = document.getElementById('catalogSearchInput');
    const searchSuggestions = document.getElementById('searchSuggestions');
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    let debounceTimer = null;

    function clearSearchInput() {
        if (!searchInput) return;
        searchInput.value = '';
        if (clearSearchBtn) clearSearchBtn.classList.add('hidden');
        if (searchSuggestions) searchSuggestions.classList.add('hidden');
        // Enviar formulario para recargar resultados limpios
        document.getElementById('catalogSearchForm').submit();
    }

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();

            // Mostrar/ocultar botón de limpiar
            if (clearSearchBtn) {
                clearSearchBtn.classList.toggle('hidden', query.length === 0);
            }

            clearTimeout(debounceTimer);

            if (query.length < 2) {
                if (searchSuggestions) searchSuggestions.classList.add('hidden');
                return;
            }

            // Consultar sugerencias y semejanzas vía endpoint
            debounceTimer = setTimeout(() => {
                fetch(`{{ route('public.catalog.suggestions') }}?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (!data || data.length === 0) {
                            searchSuggestions.innerHTML = `
                                <div class="p-4 text-center text-xs text-outline font-body">
                                    No se encontraron semejanzas directas para "<span class="font-semibold text-primary">${query}</span>".
                                    <br><span class="text-[11px]">Pulsa "Buscar equipos" para hacer una búsqueda amplia.</span>
                                </div>
                            `;
                            searchSuggestions.classList.remove('hidden');
                            return;
                        }

                        let html = `
                            <div class="px-4 py-2 bg-surface-container-low text-[10px] font-mono uppercase tracking-wider text-secondary font-bold flex items-center justify-between">
                                <span>Sugerencias y semejanzas (${data.length})</span>
                                <span class="text-[10px] lowercase text-outline">clic para autocompletar</span>
                            </div>
                        `;

                        data.forEach(item => {
                            const imgHtml = item.image_url
                                ? `<img src="${item.image_url}" class="w-9 h-9 rounded-lg object-cover shrink-0 border border-outline-variant/30" />`
                                : `<div class="w-9 h-9 rounded-lg bg-surface-container flex items-center justify-center shrink-0 text-secondary"><span class="material-symbols-outlined text-lg">devices</span></div>`;

                            html += `
                                <div class="px-4 py-2.5 hover:bg-surface-container flex items-center gap-3 cursor-pointer transition-colors"
                                     onclick="selectSuggestion('${item.name.replace(/'/g, "\\'")}')">
                                    ${imgHtml}
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-primary truncate">${item.name}</p>
                                        <p class="text-[11px] text-outline flex items-center gap-2">
                                            <span>${item.brand} ${item.model}</span>
                                            <span>·</span>
                                            <span class="text-secondary font-mono">${item.category_name}</span>
                                        </p>
                                    </div>
                                    <span class="font-mono text-[10px] bg-surface-container-low px-2 py-0.5 rounded text-outline">${item.inventory_code}</span>
                                </div>
                            `;
                        });

                        searchSuggestions.innerHTML = html;
                        searchSuggestions.classList.remove('hidden');
                    })
                    .catch(() => {
                        searchSuggestions.classList.add('hidden');
                    });
            }, 250);
        });

        // Ocultar sugerencias al hacer clic afuera
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !searchSuggestions.contains(e.target)) {
                searchSuggestions.classList.add('hidden');
            }
        });
    }

    function selectSuggestion(name) {
        if (!searchInput) return;
        searchInput.value = name;
        if (searchSuggestions) searchSuggestions.classList.add('hidden');
        if (clearSearchBtn) clearSearchBtn.classList.remove('hidden');
        document.getElementById('catalogSearchForm').submit();
    }
</script>
@endsection

@extends('layouts.public')

@section('title', 'Catálogo Institucional de Equipos y Préstamos')

@section('content')
<!-- ========================================================================= -->
<!-- SECCIÓN HERO: BIENVENIDA Y BUSCADOR DE EQUIPOS DISPONIBLES               -->
<!-- ========================================================================= -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-12" id="hero">
    <div class="p-6 sm:p-10 rounded-3xl neu-raised border border-white/80 dark:border-primary-container relative overflow-hidden">
        
        <!-- Elemento de Fondo Sutil -->
        <div class="absolute -right-16 -top-16 w-80 h-80 rounded-full bg-secondary-container/10 blur-3xl pointer-events-none"></div>

        <div class="max-w-3xl space-y-4">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full neu-inset text-xs font-mono text-secondary font-bold">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-live-dot"></span>
                <span>CATÁLOGO DE INVENTARIO VIGENTE • PERIODO ACTIVO</span>
            </div>
            
            <h1 class="font-headline text-3xl sm:text-5xl font-bold text-primary dark:text-surface tracking-tight leading-tight">
                Préstamo de Activos y Equipamiento Técnico
            </h1>
            
            <p class="font-body text-base sm:text-lg text-on-surface-variant dark:text-inverse-primary leading-relaxed">
                Consulta la disponibilidad en tiempo real de laptops, equipos de laboratorio, proyectores e instrumentación para docencia e investigación.
            </p>

            <!-- Buscador Táctil Rápido -->
            <div class="pt-4 max-w-2xl">
                <div class="neu-inset rounded-2xl p-2 flex flex-col sm:flex-row items-center gap-2">
                    <div class="flex items-center gap-2 w-full px-3 py-1">
                        <span class="material-symbols-outlined text-secondary text-2xl">search</span>
                        <input 
                            type="text" 
                            placeholder="Buscar por nombre, modelo, marca o categoría..." 
                            class="w-full bg-transparent border-0 font-body text-sm text-on-surface dark:text-surface focus:outline-none focus:ring-0 placeholder:text-outline p-0"
                        >
                    </div>
                    <button class="w-full sm:w-auto neu-btn-primary px-6 py-2.5 rounded-xl font-mono text-xs font-bold uppercase tracking-wider shrink-0 cursor-pointer">
                        Buscar
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========================================================================= -->
<!-- FILTROS Y CATÁLOGO DE EQUIPOS                                             -->
<!-- ========================================================================= -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" id="catalogo">
    
    <!-- Filtros de Categorías Táctiles -->
    <div class="flex items-center justify-between flex-wrap gap-4 mb-8">
        <div>
            <h2 class="font-headline text-2xl font-bold text-primary dark:text-surface">Equipos en Catálogo</h2>
            <p class="font-mono text-xs text-outline">Mostrando equipos registrados con disponibilidad</p>
        </div>

        <div class="flex items-center gap-2 overflow-x-auto pb-2">
            <button class="neu-raised px-4 py-2 rounded-xl font-mono text-xs font-bold text-primary dark:text-surface neu-btn cursor-pointer">
                Todos (128)
            </button>
            <button class="neu-inset px-4 py-2 rounded-xl font-mono text-xs font-bold text-secondary cursor-pointer">
                Laptops & Cómputo
            </button>
            <button class="neu-inset px-4 py-2 rounded-xl font-mono text-xs font-bold text-on-surface-variant dark:text-inverse-primary hover:text-primary cursor-pointer">
                Medición & Osciloscopios
            </button>
            <button class="neu-inset px-4 py-2 rounded-xl font-mono text-xs font-bold text-on-surface-variant dark:text-inverse-primary hover:text-primary cursor-pointer">
                Audio & Video
            </button>
        </div>
    </div>

    <!-- Rejilla de Tarjetas de Activos -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <!-- Tarjeta 1: Disponible -->
        <div class="rounded-2xl neu-raised p-6 flex flex-col justify-between neu-card-interactive border border-white/70 dark:border-primary-container">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-mono font-bold bg-[#E6F4EA] text-[#137333] border border-[#34A853]">
                        <span class="w-2 h-2 rounded-full bg-[#137333] animate-live-dot"></span>
                        Disponible
                    </span>
                    <span class="font-mono text-xs text-outline font-semibold">#AST-4019</span>
                </div>

                <div class="w-12 h-12 rounded-xl bg-primary-container text-secondary-container flex items-center justify-center neu-inset mb-4">
                    <span class="material-symbols-outlined text-2xl">laptop_mac</span>
                </div>

                <h3 class="font-headline text-lg font-bold text-primary dark:text-surface mb-1">
                    Dell Latitude 5530 i7 16GB
                </h3>
                <p class="font-body text-xs text-on-surface-variant dark:text-inverse-primary mb-4 leading-relaxed">
                    Equipo portátil para trabajo de campo y desarrollo. Incluye cargador tipo C de 65W y maletín reforzado.
                </p>

                <div class="space-y-1.5 text-xs font-mono text-outline pb-4 border-b border-outline-variant/30">
                    <div class="flex justify-between">
                        <span>Ubicación:</span>
                        <span class="font-bold text-primary dark:text-surface">Lab Cómputo 1</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Préstamo Máximo:</span>
                        <span class="font-bold text-secondary">7 Días Hábiles</span>
                    </div>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-between">
                <span class="font-mono text-xs text-secondary font-bold">Resguardo Digital</span>
                <button class="neu-btn-primary px-4 py-2 rounded-xl font-mono text-xs font-bold uppercase tracking-wider cursor-pointer">
                    Solicitar
                </button>
            </div>
        </div>

        <!-- Tarjeta 2: En Préstamo Activo -->
        <div class="rounded-2xl neu-raised p-6 flex flex-col justify-between neu-card-interactive border border-white/70 dark:border-primary-container">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-mono font-bold bg-[#FFF8E1] text-[#B06000] border border-[#E09F3E]">
                        <span class="material-symbols-outlined text-sm">schedule</span>
                        En Préstamo
                    </span>
                    <span class="font-mono text-xs text-outline font-semibold">#AST-8911</span>
                </div>

                <div class="w-12 h-12 rounded-xl bg-primary-container text-secondary-container flex items-center justify-center neu-inset mb-4">
                    <span class="material-symbols-outlined text-2xl">science</span>
                </div>

                <h3 class="font-headline text-lg font-bold text-primary dark:text-surface mb-1">
                    Osciloscopio Digital Rigol 4CH
                </h3>
                <p class="font-body text-xs text-on-surface-variant dark:text-inverse-primary mb-4 leading-relaxed">
                    Instrumento de medición de 100MHz con 4 canales analógicos. Incluye puntas de prueba atenuadas 10X.
                </p>

                <div class="space-y-1.5 text-xs font-mono text-outline pb-4 border-b border-outline-variant/30">
                    <div class="flex justify-between">
                        <span>Ubicación:</span>
                        <span class="font-bold text-primary dark:text-surface">Laboratorio de Circuitos</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Devolución Estimada:</span>
                        <span class="font-bold text-error">Mañana 14:00 hrs</span>
                    </div>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-between">
                <span class="font-mono text-xs text-outline">Próxima entrega</span>
                <button class="neu-btn-secondary px-4 py-2 rounded-xl font-mono text-xs font-bold uppercase tracking-wider cursor-pointer" disabled>
                    Reservar Fila
                </button>
            </div>
        </div>

        <!-- Tarjeta 3: Disponible -->
        <div class="rounded-2xl neu-raised p-6 flex flex-col justify-between neu-card-interactive border border-white/70 dark:border-primary-container">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-mono font-bold bg-[#E6F4EA] text-[#137333] border border-[#34A853]">
                        <span class="w-2 h-2 rounded-full bg-[#137333] animate-live-dot"></span>
                        Disponible
                    </span>
                    <span class="font-mono text-xs text-outline font-semibold">#AST-1044</span>
                </div>

                <div class="w-12 h-12 rounded-xl bg-primary-container text-secondary-container flex items-center justify-center neu-inset mb-4">
                    <span class="material-symbols-outlined text-2xl">videocam</span>
                </div>

                <h3 class="font-headline text-lg font-bold text-primary dark:text-surface mb-1">
                    Proyector Láser Epson 4000 Lumens
                </h3>
                <p class="font-body text-xs text-on-surface-variant dark:text-inverse-primary mb-4 leading-relaxed">
                    Proyector de alta definición con entradas HDMI y conexión inalámbrica. Cable de alimentación y control incluidos.
                </p>

                <div class="space-y-1.5 text-xs font-mono text-outline pb-4 border-b border-outline-variant/30">
                    <div class="flex justify-between">
                        <span>Ubicación:</span>
                        <span class="font-bold text-primary dark:text-surface">Almacén Central Audiovisual</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Préstamo Máximo:</span>
                        <span class="font-bold text-secondary">3 Días Hábiles</span>
                    </div>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-between">
                <span class="font-mono text-xs text-secondary font-bold">Entrega Inmediata</span>
                <button class="neu-btn-primary px-4 py-2 rounded-xl font-mono text-xs font-bold uppercase tracking-wider cursor-pointer">
                    Solicitar
                </button>
            </div>
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
                    Localiza el equipo en el catálogo y genera tu folio digital de solicitud con tu número de identificación o credencial institucional.
                </p>
            </div>

            <div class="p-6 rounded-2xl neu-raised text-center space-y-3">
                <div class="w-12 h-12 mx-auto rounded-xl bg-primary-container text-secondary-container flex items-center justify-center font-headline font-bold text-lg">
                    2
                </div>
                <h3 class="font-headline text-base font-bold text-primary dark:text-surface">Recepción en Ventanilla</h3>
                <p class="font-body text-xs text-on-surface-variant dark:text-inverse-primary leading-relaxed">
                    Presenta tu identificación en el módulo de entrega para verificación de accesorios, firma digital de responsiva y entrega física.
                </p>
            </div>

            <div class="p-6 rounded-2xl neu-raised text-center space-y-3">
                <div class="w-12 h-12 mx-auto rounded-xl bg-primary-container text-secondary-container flex items-center justify-center font-headline font-bold text-lg">
                    3
                </div>
                <h3 class="font-headline text-base font-bold text-primary dark:text-surface">Devolución y Checklist</h3>
                <p class="font-body text-xs text-on-surface-variant dark:text-inverse-primary leading-relaxed">
                    Entrega el equipo en la fecha pactada. El técnico validará el estado funcional y emitirá tu comprobante de liberación de resguardo.
                </p>
            </div>
        </div>
    </div>
</section>
@endsection

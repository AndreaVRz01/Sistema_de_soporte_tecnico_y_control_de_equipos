@extends('layouts.app')

@section('title', 'Dashboard Principal de Operaciones')

@section('topbar_action')
<button class="hidden sm:inline-flex items-center gap-1.5 neu-btn-primary px-3.5 py-1.5 rounded-xl font-mono text-xs font-bold shadow-md cursor-pointer">
    <span class="material-symbols-outlined text-base">add_circle</span>
    <span>Nuevo Préstamo</span>
</button>
@endsection

@section('header')
<div>
    <div class="flex items-center gap-2 mb-1">
        <span class="font-mono text-xs text-secondary font-bold uppercase tracking-wider">Telemetría Operativa</span>
        <span class="text-outline">•</span>
        <span class="font-mono text-xs text-outline">Consola Central</span>
    </div>
    <h1 class="font-headline text-2xl sm:text-3xl font-bold text-primary dark:text-surface">
        Tablero de Control de Activos & Préstamos
    </h1>
</div>

<div class="flex items-center gap-2">
    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full neu-inset text-xs font-mono text-secondary font-semibold">
        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-live-dot"></span>
        Actualización en Tiempo Real
    </span>
    <button class="p-2 rounded-xl neu-raised text-primary dark:text-surface neu-btn cursor-pointer" title="Recargar Métricas">
        <span class="material-symbols-outlined text-base">refresh</span>
    </button>
</div>
@endsection

@section('content')
<!-- ========================================================================= -->
<!-- TARJETAS DE MÉTRICAS KPI (Telemetría de Hardware)                        -->
<!-- ========================================================================= -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    
    <!-- Métrica 1: Total de Activos -->
    <div class="p-5 rounded-2xl neu-raised neu-card-interactive border border-white/70 dark:border-primary-container">
        <div class="flex items-center justify-between mb-3">
            <span class="font-mono text-xs text-outline font-semibold uppercase tracking-wider">Activos Totales</span>
            <div class="w-8 h-8 rounded-lg bg-primary-container text-secondary-container flex items-center justify-center neu-inset">
                <span class="material-symbols-outlined text-lg">inventory_2</span>
            </div>
        </div>
        <div class="flex items-baseline justify-between">
            <span class="font-headline text-3xl font-bold text-primary dark:text-surface">1,248</span>
            <span class="font-mono text-xs text-emerald-600 font-semibold flex items-center">
                <span class="material-symbols-outlined text-sm">trending_up</span> +12 este mes
            </span>
        </div>
        <p class="font-body text-xs text-outline mt-2">Hardware inventariado y verificado</p>
    </div>

    <!-- Métrica 2: En Préstamo Activo -->
    <div class="p-5 rounded-2xl neu-raised neu-card-interactive border border-white/70 dark:border-primary-container">
        <div class="flex items-center justify-between mb-3">
            <span class="font-mono text-xs text-secondary font-semibold uppercase tracking-wider">Préstamos Vigentes</span>
            <div class="w-8 h-8 rounded-lg bg-secondary-container text-primary flex items-center justify-center neu-inset">
                <span class="material-symbols-outlined text-lg">handshake</span>
            </div>
        </div>
        <div class="flex items-baseline justify-between">
            <span class="font-headline text-3xl font-bold text-secondary">84</span>
            <span class="font-mono text-xs text-amber-600 font-semibold flex items-center">
                6 por vencer hoy
            </span>
        </div>
        <p class="font-body text-xs text-outline mt-2">Equipos bajo resguardo temporal</p>
    </div>

    <!-- Métrica 3: En Mantenimiento -->
    <div class="p-5 rounded-2xl neu-raised neu-card-interactive border border-white/70 dark:border-primary-container">
        <div class="flex items-center justify-between mb-3">
            <span class="font-mono text-xs text-error font-semibold uppercase tracking-wider">Mantenimiento</span>
            <div class="w-8 h-8 rounded-lg bg-error-container text-on-error-container flex items-center justify-center neu-inset">
                <span class="material-symbols-outlined text-lg">build</span>
            </div>
        </div>
        <div class="flex items-baseline justify-between">
            <span class="font-headline text-3xl font-bold text-error">15</span>
            <span class="font-mono text-xs text-error font-semibold">
                3 críticos
            </span>
        </div>
        <p class="font-body text-xs text-outline mt-2">En diagnóstico o reparación activa</p>
    </div>

    <!-- Métrica 4: Cumplimiento de Devoluciones -->
    <div class="p-5 rounded-2xl neu-raised neu-card-interactive border border-white/70 dark:border-primary-container">
        <div class="flex items-center justify-between mb-3">
            <span class="font-mono text-xs text-outline font-semibold uppercase tracking-wider">Tasa Retorno Puntual</span>
            <div class="w-8 h-8 rounded-lg bg-surface-container text-secondary flex items-center justify-center neu-inset">
                <span class="material-symbols-outlined text-lg">verified</span>
            </div>
        </div>
        <div class="flex items-baseline justify-between">
            <span class="font-headline text-3xl font-bold text-primary dark:text-surface">97.4%</span>
            <span class="font-mono text-xs text-emerald-600 font-semibold">
                Óptimo
            </span>
        </div>
        <p class="font-body text-xs text-outline mt-2">Índice de entrega en tiempo y forma</p>
    </div>
</div>

<!-- ========================================================================= -->
<!-- TABLA DE ALTA DENSIDAD: PRÉSTAMOS ACTIVOS CON SEGUIMIENTO DE TIEMPO       -->
<!-- ========================================================================= -->
<div class="p-6 rounded-2xl neu-raised border border-white/70 dark:border-primary-container mb-8">
    
    <div class="flex items-center justify-between flex-wrap gap-4 pb-4 border-b border-outline-variant/30 mb-4">
        <div>
            <h2 class="font-headline text-lg font-bold text-primary dark:text-surface">Préstamos Activos & Vencimientos Próximos</h2>
            <p class="font-mono text-xs text-outline">Seguimiento en tiempo real de equipos entregados con folio oficial</p>
        </div>

        <div class="flex items-center gap-2">
            <div class="neu-inset rounded-xl px-3 py-1.5 flex items-center gap-2">
                <span class="material-symbols-outlined text-outline text-base">filter_list</span>
                <input type="text" placeholder="Filtrar préstamos..." class="bg-transparent border-0 font-body text-xs text-on-surface dark:text-surface focus:outline-none focus:ring-0 p-0">
            </div>
            <button class="neu-btn-secondary px-3 py-1.5 rounded-xl font-mono text-xs font-semibold cursor-pointer">
                Exportar CSV
            </button>
        </div>
    </div>

    <!-- Tabla Responsiva -->
    <div class="overflow-x-auto">
        <table class="w-full text-left font-body text-xs">
            <thead>
                <tr class="font-mono text-[11px] text-outline uppercase tracking-wider border-b border-outline-variant/30">
                    <th class="py-3 px-3">Folio Préstamo</th>
                    <th class="py-3 px-3">Activo / Serie</th>
                    <th class="py-3 px-3">Solicitante</th>
                    <th class="py-3 px-3">Fecha Préstamo</th>
                    <th class="py-3 px-3">Fecha Límite</th>
                    <th class="py-3 px-3">Estado Operativo</th>
                    <th class="py-3 px-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/20">
                
                <!-- Fila 1 -->
                <tr class="hover:bg-surface-container-low/50 dark:hover:bg-primary-container/40 transition-colors">
                    <td class="py-3.5 px-3 font-mono font-bold text-primary dark:text-surface">#PR-2026-089</td>
                    <td class="py-3.5 px-3">
                        <div class="font-bold text-primary dark:text-surface">MacBook Pro M2 Max 16"</div>
                        <span class="font-mono text-[11px] text-outline">SN-9821-X</span>
                    </td>
                    <td class="py-3.5 px-3">
                        <div class="font-semibold text-primary dark:text-surface">Dra. Claudia Benítez</div>
                        <span class="text-outline text-[11px]">Depto. Inteligencia Artificial</span>
                    </td>
                    <td class="py-3.5 px-3 font-mono">24 Oct, 09:30</td>
                    <td class="py-3.5 px-3 font-mono font-bold text-secondary">Hoy, 18:00 hrs</td>
                    <td class="py-3.5 px-3">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-mono text-[11px] font-bold bg-[#FFF8E1] text-[#B06000] border border-[#E09F3E]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#B06000]"></span>
                            Por Vencer
                        </span>
                    </td>
                    <td class="py-3.5 px-3 text-right">
                        <button class="neu-btn-primary px-3 py-1 rounded-lg font-mono text-[11px] font-semibold cursor-pointer">
                            Devolver
                        </button>
                    </td>
                </tr>

                <!-- Fila 2 -->
                <tr class="hover:bg-surface-container-low/50 dark:hover:bg-primary-container/40 transition-colors">
                    <td class="py-3.5 px-3 font-mono font-bold text-primary dark:text-surface">#PR-2026-088</td>
                    <td class="py-3.5 px-3">
                        <div class="font-bold text-primary dark:text-surface">Osciloscopio Digital Rigol 4CH</div>
                        <span class="font-mono text-[11px] text-outline">SN-4892-A</span>
                    </td>
                    <td class="py-3.5 px-3">
                        <div class="font-semibold text-primary dark:text-surface">Ing. Roberto Sánchez</div>
                        <span class="text-outline text-[11px]">Lab de Robótica y Control</span>
                    </td>
                    <td class="py-3.5 px-3 font-mono">22 Oct, 11:15</td>
                    <td class="py-3.5 px-3 font-mono text-outline">29 Oct, 14:00 hrs</td>
                    <td class="py-3.5 px-3">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-mono text-[11px] font-bold bg-[#E6F4EA] text-[#137333] border border-[#34A853]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#137333] animate-live-dot"></span>
                            En Plazo
                        </span>
                    </td>
                    <td class="py-3.5 px-3 text-right">
                        <button class="neu-btn-secondary px-3 py-1 rounded-lg font-mono text-[11px] font-semibold cursor-pointer">
                            Detalles
                        </button>
                    </td>
                </tr>

                <!-- Fila 3: Retrasado / Crítico -->
                <tr class="bg-red-50/40 dark:bg-error-container/10 hover:bg-red-50 dark:hover:bg-error-container/20 transition-colors">
                    <td class="py-3.5 px-3 font-mono font-bold text-error">#PR-2026-082</td>
                    <td class="py-3.5 px-3">
                        <div class="font-bold text-primary dark:text-surface">Generador de Funciones RF</div>
                        <span class="font-mono text-[11px] text-error font-semibold">SN-1022-RF</span>
                    </td>
                    <td class="py-3.5 px-3">
                        <div class="font-semibold text-primary dark:text-surface">Mtro. Fernando Vega</div>
                        <span class="text-outline text-[11px]">Lab Telecomunicaciones</span>
                    </td>
                    <td class="py-3.5 px-3 font-mono">18 Oct, 14:00</td>
                    <td class="py-3.5 px-3 font-mono font-bold text-error">21 Oct (Vencido)</td>
                    <td class="py-3.5 px-3">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-mono text-[11px] font-bold bg-[#FCE8E6] text-[#9E2A2B] border border-[#EA4335]">
                            <span class="material-symbols-outlined text-xs">warning</span>
                            Demorado (+5 días)
                        </span>
                    </td>
                    <td class="py-3.5 px-3 text-right">
                        <button class="neu-btn-primary bg-error px-3 py-1 rounded-lg font-mono text-[11px] font-semibold text-white cursor-pointer">
                            Notificar
                        </button>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>
</div>
@endsection

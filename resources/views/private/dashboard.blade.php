@extends('layouts.app')

@section('title', 'Dashboard Principal de Control y Operaciones')

@section('topbar_action')
    @if (in_array(Auth::user()->role, ['admin', 'supervisor', 'encargado_prestamos'], true))
        <a href="{{ route('loans.index') }}" class="hidden sm:inline-flex items-center gap-1.5 neu-btn-primary px-3.5 py-1.5 rounded-xl font-mono text-xs font-bold shadow-md cursor-pointer">
            <span class="material-symbols-outlined text-base">add_circle</span>
            <span>Nuevo Préstamo</span>
        </a>
    @endif
@endsection

@section('header')
<div>
    <div class="flex items-center gap-2 mb-1">
        <span class="font-mono text-xs text-secondary font-bold uppercase tracking-wider">Telemetría Operativa</span>
        <span class="text-outline">•</span>
        <span class="font-mono text-xs text-outline">Consola Central SIGA</span>
    </div>
    <h1 class="font-headline text-2xl sm:text-3xl font-bold text-primary dark:text-surface">
        Tablero de Control de Activos & Préstamos
    </h1>
</div>

<div class="flex items-center gap-2">
    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full neu-inset text-xs font-mono text-secondary font-semibold">
        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-live-dot"></span>
        Audit Stream Activo
    </span>
    <a href="{{ route('dashboard') }}" class="p-2 rounded-xl neu-raised text-primary dark:text-surface neu-btn cursor-pointer" title="Recargar Métricas">
        <span class="material-symbols-outlined text-base">refresh</span>
    </a>
</div>
@endsection

@section('content')
<!-- ========================================================================= -->
<!-- TARJETAS DE MÉTRICAS KPI (Telemetría de Hardware)                        -->
<!-- ========================================================================= -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    
    <!-- Métrica 1: Total de Activos -->
    @if (in_array(Auth::user()->role, ['admin', 'supervisor', 'encargado_activos'], true))
    <a href="{{ route('equipment.index') }}" class="p-5 rounded-2xl neu-raised neu-card-interactive border border-white/70 dark:border-primary-container block">
        <div class="flex items-center justify-between mb-3">
            <span class="font-mono text-xs text-outline font-semibold uppercase tracking-wider">Activos Patrimoniales</span>
            <div class="w-8 h-8 rounded-lg bg-primary-container text-secondary-container flex items-center justify-center neu-inset">
                <span class="material-symbols-outlined text-lg">inventory_2</span>
            </div>
        </div>
        <div class="flex items-baseline justify-between">
            <span class="font-headline text-3xl font-bold text-primary dark:text-surface">{{ $totalEquipment }}</span>
            <span class="font-mono text-xs text-emerald-600 font-semibold flex items-center gap-0.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> {{ $availableEquipment }} disponibles
            </span>
        </div>
        <p class="font-body text-xs text-outline mt-2">Inventario catalogado y activo</p>
    </a>
    @endif

    <!-- Métrica 2: En Préstamo Activo -->
    @if (in_array(Auth::user()->role, ['admin', 'supervisor', 'encargado_prestamos'], true))
    <a href="{{ route('loans.index', ['status' => 'entregado']) }}" class="p-5 rounded-2xl neu-raised neu-card-interactive border border-white/70 dark:border-primary-container block">
        <div class="flex items-center justify-between mb-3">
            <span class="font-mono text-xs text-secondary font-semibold uppercase tracking-wider">Préstamos Vigentes</span>
            <div class="w-8 h-8 rounded-lg bg-secondary-container text-primary flex items-center justify-center neu-inset">
                <span class="material-symbols-outlined text-lg">handshake</span>
            </div>
        </div>
        <div class="flex items-baseline justify-between">
            <span class="font-headline text-3xl font-bold text-secondary">{{ $activeLoansCount }}</span>
            <span class="font-mono text-xs text-amber-600 font-semibold">
                {{ $criticalAlertLoans->count() }} críticos
            </span>
        </div>
        <p class="font-body text-xs text-outline mt-2">Bienes en custodia temporal</p>
    </a>
    @endif

    <!-- Métrica 3: En Mantenimiento -->
    @if (in_array(Auth::user()->role, ['admin', 'supervisor', 'encargado_activos'], true))
    <a href="{{ route('incidents.index') }}" class="p-5 rounded-2xl neu-raised neu-card-interactive border border-white/70 dark:border-primary-container block">
        <div class="flex items-center justify-between mb-3">
            <span class="font-mono text-xs text-error font-semibold uppercase tracking-wider">Taller & Tickets</span>
            <div class="w-8 h-8 rounded-lg bg-error-container text-on-error-container flex items-center justify-center neu-inset">
                <span class="material-symbols-outlined text-lg">build</span>
            </div>
        </div>
        <div class="flex items-baseline justify-between">
            <span class="font-headline text-3xl font-bold text-error">{{ $inMaintenanceCount }}</span>
            <span class="font-mono text-xs text-error font-semibold">
                {{ $openIncidentsCount }} tickets abiertos
            </span>
        </div>
        <p class="font-body text-xs text-outline mt-2">En diagnóstico o reparación</p>
    </a>
    @endif

    <!-- Métrica 4: Sanciones & Alertas -->
    @if (in_array(Auth::user()->role, ['admin', 'supervisor', 'encargado_activos'], true))
    <a href="{{ route('sanctions.index') }}" class="p-5 rounded-2xl neu-raised neu-card-interactive border border-white/70 dark:border-primary-container block">
        <div class="flex items-center justify-between mb-3">
            <span class="font-mono text-xs text-outline font-semibold uppercase tracking-wider">Sanciones Activas</span>
            <div class="w-8 h-8 rounded-lg bg-surface-container text-secondary flex items-center justify-center neu-inset">
                <span class="material-symbols-outlined text-lg">gavel</span>
            </div>
        </div>
        <div class="flex items-baseline justify-between">
            <span class="font-headline text-3xl font-bold text-primary dark:text-surface">{{ $activeSanctionsCount }}</span>
            <span class="font-mono text-xs {{ $overdueLoansCount > 0 ? 'text-error font-bold' : 'text-emerald-600' }}">
                {{ $overdueLoansCount }} préstamos vencidos
            </span>
        </div>
        <p class="font-body text-xs text-outline mt-2">Usuarios con suspensión preventiva</p>
    </a>
    @endif
</div>

<!-- ========================================================================= -->
<!-- SECCIÓN DE ALERTAS CRÍTICAS: PRÓXIMOS A VENCER O CON RETRASO            -->
<!-- ========================================================================= -->
@if (in_array(Auth::user()->role, ['admin', 'supervisor', 'encargado_prestamos'], true))
<div class="p-6 rounded-2xl neu-raised border border-white/70 dark:border-primary-container mb-8">
    <div class="flex items-center justify-between flex-wrap gap-4 pb-4 border-b border-outline-variant/30 mb-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-error text-xl animate-pulse">warning</span>
                <h2 class="font-headline text-lg font-bold text-primary dark:text-surface">Alertas: Vencimientos Críticos & Seguimiento de Tiempo</h2>
            </div>
            <p class="font-mono text-xs text-outline">Préstamos con fecha límite rebasada o con vencimiento en menos de 24 horas</p>
        </div>

        <a href="{{ route('loans.index', ['status' => 'vencido']) }}" class="neu-btn-secondary px-3 py-1.5 rounded-xl font-mono text-xs font-semibold">
            Ver Todos los Vencidos
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left font-body text-xs">
            <thead>
                <tr class="font-mono text-[11px] text-outline uppercase tracking-wider border-b border-outline-variant/30">
                    <th class="py-3 px-3">Folio Acta</th>
                    <th class="py-3 px-3">Activos Amparados</th>
                    <th class="py-3 px-3">Custodio</th>
                    <th class="py-3 px-3">Fecha Límite</th>
                    <th class="py-3 px-3">Estado Operativo</th>
                    <th class="py-3 px-3 text-right">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/20">
                @forelse ($criticalAlertLoans as $alertLoan)
                    @php
                        $isLate = $alertLoan->isOverdue();
                    @endphp
                    <tr class="{{ $isLate ? 'bg-red-50/50 dark:bg-error-container/10' : 'hover:bg-surface-container-low/40' }} transition-colors">
                        <td class="py-3.5 px-3 font-mono font-bold text-primary dark:text-surface">
                            <a href="{{ route('loans.show', $alertLoan) }}" class="hover:underline">
                                {{ $alertLoan->folio }}
                            </a>
                        </td>
                        <td class="py-3.5 px-3">
                            @foreach ($alertLoan->items as $it)
                                <div class="font-semibold text-primary dark:text-surface">{{ $it->equipment->name ?? 'Activo' }}</div>
                                <span class="font-mono text-[10px] text-outline">SN: {{ $it->equipment->serial_number ?? 'N/A' }}</span>
                            @endforeach
                        </td>
                        <td class="py-3.5 px-3">
                            <div class="font-bold text-primary dark:text-surface">{{ $alertLoan->user->name ?? 'Usuario' }}</div>
                            <span class="text-outline text-[11px]">{{ $alertLoan->user->institutional_id ?? 'N/A' }}</span>
                        </td>
                        <td class="py-3.5 px-3 font-mono {{ $isLate ? 'text-error font-bold' : 'text-secondary font-bold' }}">
                            {{ $alertLoan->expected_return_date->format('d/m/Y H:i') }}
                        </td>
                        <td class="py-3.5 px-3">
                            @if ($isLate)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-mono text-[10px] font-bold bg-[#FCE8E6] text-[#9E2A2B] border border-[#EA4335]">
                                    <span class="material-symbols-outlined text-xs">error</span>
                                    Vencido
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-mono text-[10px] font-bold bg-[#FFF8E1] text-[#B06000] border border-[#E09F3E]">
                                    <span class="material-symbols-outlined text-xs">schedule</span>
                                    Vence Hoy
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-3 text-right">
                            <a href="{{ route('loans.show', $alertLoan) }}" class="neu-btn-primary px-3 py-1 rounded-lg font-mono text-[11px] font-semibold inline-block">
                                Gestionar
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-outline">
                            <span class="material-symbols-outlined text-emerald-600 text-2xl mb-1 block">check_circle</span>
                            <p class="font-headline font-semibold text-xs text-emerald-800">No hay préstamos con vencimientos críticos o en mora.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- ========================================================================= -->
<!-- CUADRÍCULA INFERIOR: DISTRIBUCIÓN POR ÁREAS & STREAM DE AUDITORÍA         -->
<!-- ========================================================================= -->
@if (in_array(Auth::user()->role, ['admin', 'supervisor', 'encargado_activos'], true))
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    
    <!-- Distribución de Equipos por Categoría y Ubicación -->
    <div class="{{ in_array(Auth::user()->role, ['admin', 'supervisor'], true) ? 'lg:col-span-6' : 'lg:col-span-12' }} p-6 rounded-2xl neu-raised border border-white/70 dark:border-primary-container space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30">
            <h3 class="font-headline text-base font-bold text-primary dark:text-surface">Distribución por Categorías</h3>
            <a href="{{ route('equipment.index') }}" class="font-mono text-xs text-secondary hover:underline">Ver Inventario</a>
        </div>

        <div class="space-y-3">
            @foreach ($categoriesStats as $cat)
                @php
                    $pct = $totalEquipment > 0 ? round(($cat->equipment_count / $totalEquipment) * 100) : 0;
                @endphp
                <div class="space-y-1">
                    <div class="flex justify-between font-mono text-xs">
                        <span class="font-bold text-primary dark:text-surface">{{ $cat->name }}</span>
                        <span class="text-outline">{{ $cat->equipment_count }} equipos ({{ $cat->available_equipment_count }} disp.)</span>
                    </div>
                    <div class="w-full h-2 rounded-full neu-inset overflow-hidden">
                        <div class="h-full bg-secondary rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="pt-4 border-t border-outline-variant/30">
            <span class="font-mono text-[11px] text-outline uppercase font-bold block mb-2">Principales Ubicaciones Físicas</span>
            <div class="grid grid-cols-2 gap-2">
                @foreach ($locationsStats as $loc)
                    <div class="p-2.5 rounded-xl neu-inset font-mono text-xs flex justify-between items-center">
                        <span class="text-primary truncate">{{ $loc->location }}</span>
                        <span class="font-bold text-secondary ml-1">{{ $loc->total }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Bitácora y Auditoría en Tiempo Real -->
    @if (in_array(Auth::user()->role, ['admin', 'supervisor'], true))
    <div class="lg:col-span-6 p-6 rounded-2xl neu-raised border border-white/70 dark:border-primary-container space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary text-lg">verified_user</span>
                <h3 class="font-headline text-base font-bold text-primary dark:text-surface">Bitácora en Tiempo Real</h3>
            </div>
            <a href="{{ route('audit.index') }}" class="font-mono text-xs text-secondary hover:underline">Auditoría Completa</a>
        </div>

        <div class="space-y-2.5">
            @forelse ($recentAuditStream as $log)
                <div class="p-3 rounded-xl neu-inset flex items-start justify-between gap-3 font-body text-xs">
                    <div class="space-y-0.5">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-[10px] font-bold text-secondary">{{ $log->action }}</span>
                            <span class="text-outline text-[10px]">•</span>
                            <span class="font-mono text-[10px] text-outline">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-primary dark:text-surface text-[11px] line-clamp-1">{{ $log->description }}</p>
                        <span class="font-mono text-[10px] text-outline">Operador: {{ $log->user->name ?? 'Sistema' }}</span>
                    </div>
                    <span class="px-2 py-0.5 rounded font-mono text-[9px] font-bold uppercase shrink-0
                        @if ($log->risk_level === 'critico') bg-error-container text-on-error-container
                        @elseif ($log->risk_level === 'advertencia') bg-amber-100 text-amber-900
                        @else bg-surface-container text-primary @endif">
                        {{ $log->risk_level }}
                    </span>
                </div>
            @empty
                <p class="text-outline text-xs italic text-center py-6">No hay registros recientes en la bitácora.</p>
            @endforelse
        </div>
    </div>
    @endif

</div>
@endif
@endsection

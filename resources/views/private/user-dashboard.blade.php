@extends('layouts.app')

@section('title', 'Mi panel')

@section('header')
    <div>
        <p class="font-mono text-[11px] text-secondary font-bold uppercase">Portal personal</p>
        <h1 class="font-headline text-xl sm:text-2xl font-bold text-primary dark:text-surface">Hola, {{ $user->name }}</h1>
        <p class="font-body text-xs text-on-surface-variant dark:text-inverse-primary mt-1">Consulta tus préstamos, reportes y actividad institucional.</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('portal.reports.create') }}" class="neu-btn-secondary px-3.5 py-2 rounded-xl font-mono text-xs font-bold text-secondary inline-flex items-center gap-1.5">
            <span class="material-symbols-outlined text-base">report</span><span>Enviar reporte</span>
        </a>
        <a href="{{ route('public.catalog') }}" class="neu-btn-primary px-3.5 py-2 rounded-xl font-mono text-xs font-bold inline-flex items-center gap-1.5">
            <span class="material-symbols-outlined text-base">inventory_2</span><span>Solicitar préstamo</span>
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6">
    @php
        $rejectedList = $rejectedLoans ?? collect();
    @endphp

    @if ($rejectedList->isNotEmpty())
        <section class="rounded-2xl bg-error-container border border-error/35 text-on-error-container p-4 neu-raised space-y-2" aria-label="Solicitudes de préstamo no aceptadas">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-error text-xl">cancel</span>
                <h2 class="font-headline text-sm font-bold">Tienes {{ $rejectedList->count() === 1 ? 'una solicitud de préstamo no aceptada' : $rejectedList->count() . ' solicitudes de préstamo no aceptadas' }}</h2>
            </div>
            <ul class="space-y-1.5 pl-7 text-xs font-body">
                @foreach ($rejectedList as $rejected)
                    <li>
                        <strong class="font-mono">{{ $rejected->folio }}</strong>:
                        {{ $rejected->rejection_reason ?: 'Tu solicitud no fue aceptada por el área de préstamos.' }}
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <section class="grid grid-cols-1 sm:grid-cols-3 gap-3" aria-label="Resumen personal">
        <div class="neu-raised rounded-xl p-4"><p class="font-mono text-[10px] text-outline uppercase font-semibold">Mis préstamos</p><p class="font-headline text-2xl font-bold text-primary dark:text-surface mt-1">{{ $loanCounts['total'] }}</p></div>
        <div class="neu-raised rounded-xl p-4"><p class="font-mono text-[10px] text-secondary uppercase font-semibold">En proceso</p><p class="font-headline text-2xl font-bold text-secondary mt-1">{{ $loanCounts['pending'] }}</p></div>
        <div class="neu-raised rounded-xl p-4"><p class="font-mono text-[10px] text-emerald-700 uppercase font-semibold">En mi resguardo</p><p class="font-headline text-2xl font-bold text-emerald-700 mt-1">{{ $loanCounts['active'] }}</p></div>
    </section>

    <section class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="neu-raised rounded-2xl overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b border-outline-variant/30">
                <div><h2 class="font-headline font-bold text-primary dark:text-surface">Mis préstamos</h2><p class="font-body text-xs text-outline mt-0.5">Solicitudes y activos asignados a tu cuenta.</p></div>
                <a href="{{ route('public.catalog') }}" class="font-mono text-xs font-bold text-secondary">Ver catálogo</a>
            </div>
            <div class="divide-y divide-outline-variant/20">
                @forelse ($myLoans as $loan)
                    @php
                        $badgeClasses = match ($loan->status) {
                            'rechazado' => 'bg-error-container text-on-error-container border border-error/40',
                            'entregado' => 'bg-emerald-50 text-emerald-800 border border-emerald-300',
                            'aprobado' => 'bg-blue-50 text-blue-800 border border-blue-300',
                            'vencido' => 'bg-error-container text-on-error-container border border-error/40',
                            default => 'bg-surface-container text-secondary',
                        };
                        $statusLabel = $loan->status === 'rechazado'
                            ? 'No aceptado'
                            : ucfirst(str_replace('_', ' ', $loan->status));
                    @endphp
                    <div class="p-4 flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="font-mono text-xs font-bold text-primary dark:text-surface">{{ $loan->folio }}</p>
                            <p class="font-body text-xs text-on-surface-variant mt-1">{{ $loan->purpose }}</p>
                            <p class="font-mono text-[10px] text-outline mt-1">{{ $loan->items->pluck('equipment.name')->filter()->join(', ') ?: 'Equipo por confirmar' }}</p>
                            @if ($loan->status === 'rechazado')
                                <p class="mt-2 rounded-lg bg-error-container/60 border border-error/30 px-2.5 py-1.5 font-body text-xs text-on-error-container">
                                    <strong>Motivo:</strong> {{ $loan->rejection_reason ?: 'No especificado.' }}
                                </p>
                            @endif
                        </div>
                        <span class="shrink-0 rounded-full px-2.5 py-1 font-mono text-[10px] font-bold {{ $badgeClasses }}">{{ $statusLabel }}</span>
                    </div>
                @empty
                    <p class="p-5 font-body text-sm text-outline">Todavía no tienes préstamos registrados.</p>
                @endforelse
            </div>
        </div>

        <div class="neu-raised rounded-2xl overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b border-outline-variant/30">
                <div><h2 class="font-headline font-bold text-primary dark:text-surface">Mis reportes</h2><p class="font-body text-xs text-outline mt-0.5">Seguimiento de incidencias que has enviado.</p></div>
                <a href="{{ route('portal.reports.create') }}" class="font-mono text-xs font-bold text-secondary">Nuevo reporte</a>
            </div>
            <div class="divide-y divide-outline-variant/20">
                @forelse ($myReports as $report)
                    <div class="p-4 flex items-start justify-between gap-3">
                        <div><p class="font-mono text-xs font-bold text-primary dark:text-surface">{{ $report->ticket_code }}</p><p class="font-body text-sm font-semibold text-on-surface mt-1">{{ $report->title }}</p><p class="font-mono text-[10px] text-outline mt-1">{{ $report->equipment->name ?? 'Activo eliminado' }} · {{ $report->reported_at->format('d/m/Y') }}</p></div>
                        <span class="shrink-0 rounded-full bg-surface-container px-2.5 py-1 font-mono text-[10px] font-bold text-secondary">{{ ucfirst(str_replace('_', ' ', $report->status)) }}</span>
                    </div>
                @empty
                    <p class="p-5 font-body text-sm text-outline">Aún no has enviado reportes.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="neu-raised rounded-2xl overflow-hidden">
        <div class="flex items-center justify-between p-4 border-b border-outline-variant/30">
            <div><h2 class="font-headline font-bold text-primary dark:text-surface">Actividad reciente</h2><p class="font-body text-xs text-outline mt-0.5">Registro individual de tus solicitudes y acciones.</p></div>
            <a href="{{ route('portal.activity') }}" class="font-mono text-xs font-bold text-secondary">Abrir mi bitácora</a>
        </div>
        <div class="divide-y divide-outline-variant/20">
            @forelse ($recentActivity as $entry)
                <div class="px-4 py-3 flex items-center justify-between gap-3"><span class="font-body text-sm text-on-surface">{{ $entry->displayName() }}</span><time class="font-mono text-[10px] text-outline">{{ $entry->created_at->format('d/m/Y H:i') }}</time></div>
            @empty
                <p class="p-4 font-body text-sm text-outline">Tu actividad aparecerá aquí.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
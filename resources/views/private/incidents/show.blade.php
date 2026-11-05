@extends('layouts.app')

@section('title', 'Orden de Mantenimiento ' . $incident->ticket_code)

@section('header')
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('incidents.index') }}" class="text-secondary hover:text-primary flex items-center font-mono text-xs gap-1">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                <span>Volver a Tickets</span>
            </a>
            <span class="text-outline text-xs">•</span>
            <span class="font-mono text-xs text-outline">Hoja de Taller y Diagnóstico</span>
        </div>
        <h1 class="font-headline text-xl sm:text-2xl font-bold text-primary dark:text-surface tracking-tight flex items-center gap-2">
            <span>Ticket: {{ $incident->ticket_code }}</span>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold
                @if (in_array($incident->priority, ['critica', 'alta'])) bg-error-container text-on-error-container
                @else bg-blue-100 text-blue-800 @endif">
                Prioridad {{ ucfirst($incident->priority) }}
            </span>
        </h1>
    </div>

    <div class="flex items-center gap-2">
        <span class="px-3 py-1.5 rounded-xl neu-inset font-mono text-xs font-bold text-primary">
            Estatus: {{ strtoupper(str_replace('_', ' ', $incident->status)) }}
        </span>
    </div>
@endsection

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Cuadrícula Superior: Ficha del Activo & Datos de la Avería -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Ficha Técnica del Activo Afectado -->
        <div class="lg:col-span-5 neu-raised rounded-2xl p-6 space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-outline-variant/30">
                <span class="material-symbols-outlined text-secondary text-xl">devices</span>
                <h3 class="font-headline text-base font-bold text-primary dark:text-surface">Activo en Intervención</h3>
            </div>

            <div class="space-y-2 font-mono text-xs">
                <div>
                    <span class="text-outline block text-[10px]">Nombre del Bien:</span>
                    <span class="font-bold text-sm text-primary dark:text-surface">{{ $incident->equipment->name }}</span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <span class="text-outline block text-[10px]">ID Inventario:</span>
                        <span class="font-bold text-secondary">{{ $incident->equipment->inventory_code }}</span>
                    </div>
                    <div>
                        <span class="text-outline block text-[10px]">Número de Serie:</span>
                        <span class="font-bold text-primary">{{ $incident->equipment->serial_number }}</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <span class="text-outline block text-[10px]">Marca / Modelo:</span>
                        <span>{{ $incident->equipment->brand }} {{ $incident->equipment->model }}</span>
                    </div>
                    <div>
                        <span class="text-outline block text-[10px]">Ubicación:</span>
                        <span>{{ $incident->equipment->location }}</span>
                    </div>
                </div>
            </div>

            <!-- Asignación de Técnico -->
            <div class="pt-3 border-t border-outline-variant/30 space-y-2">
                <span class="font-mono text-[10px] text-outline uppercase font-bold block">Técnico Responsable</span>
                @if ($incident->technician)
                    <div class="flex items-center justify-between p-2.5 rounded-xl neu-inset">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-secondary text-lg">engineering</span>
                            <div>
                                <p class="font-headline font-bold text-xs text-primary">{{ $incident->technician->name }}</p>
                                <p class="font-mono text-[10px] text-outline">{{ $incident->technician->email }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="p-2.5 rounded-xl neu-inset text-center text-xs text-outline italic">
                        Sin técnico asignado actualmente.
                    </div>
                @endif

                <!-- Formulario Rápido de Asignación -->
                <form method="POST" action="{{ route('incidents.assign', $incident) }}" class="flex gap-2 pt-1">
                    @csrf
                    <select name="assigned_technician_id" required class="neu-inset flex-1 px-3 py-1.5 rounded-xl text-xs">
                        <option value="">Reasignar a otro técnico...</option>
                        @foreach ($technicians as $tech)
                            <option value="{{ $tech->id }}" {{ $incident->assigned_technician_id === $tech->id ? 'selected' : '' }}>
                                {{ $tech->name }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="neu-btn-secondary px-3 py-1.5 rounded-xl font-mono text-xs font-bold text-secondary hover:text-primary">
                        Asignar
                    </button>
                </form>
            </div>
        </div>

        <!-- Síntomas y Diagnóstico Técnico -->
        <div class="lg:col-span-7 neu-raised rounded-2xl p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary text-xl">description</span>
                    <h3 class="font-headline text-base font-bold text-primary dark:text-surface">Descripción y Diagnóstico</h3>
                </div>
                <span class="font-mono text-xs text-outline">{{ $incident->reported_at->format('d/m/Y H:i') }}</span>
            </div>

            <div class="space-y-3 font-body text-xs">
                <div>
                    <span class="font-mono text-[10px] text-outline uppercase font-bold block">Falla Reportada:</span>
                    <p class="font-headline font-bold text-sm text-primary dark:text-surface mt-0.5">{{ $incident->title }}</p>
                    <p class="text-on-surface-variant mt-1 leading-relaxed">{{ $incident->description }}</p>
                </div>

                @if ($incident->diagnostic)
                    <div class="p-3 rounded-xl neu-inset bg-surface-container-low space-y-1">
                        <span class="font-mono text-[10px] text-secondary font-bold uppercase block">Diagnóstico de Causa Raíz</span>
                        <p class="text-on-surface leading-relaxed">{{ $incident->diagnostic }}</p>
                    </div>
                @endif

                @if ($incident->solution_applied)
                    <div class="p-3 rounded-xl neu-inset bg-emerald-50 border border-emerald-200 space-y-1">
                        <span class="font-mono text-[10px] text-emerald-800 font-bold uppercase block">Solución Definitiva Aplicada</span>
                        <p class="text-emerald-950 leading-relaxed">{{ $incident->solution_applied }}</p>
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-3 pt-2 font-mono text-xs border-t border-outline-variant/30">
                    <div>
                        <span class="text-outline text-[10px] block">Costo Estimado:</span>
                        <span class="font-bold text-primary">${{ number_format($incident->estimated_cost ?? 0, 2) }} MXN</span>
                    </div>
                    <div>
                        <span class="text-outline text-[10px] block">Costo Real:</span>
                        <span class="font-bold text-secondary">${{ number_format($incident->actual_cost ?? 0, 2) }} MXN</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Bitácora Cronológica de Intervenciones Técnicas (Logs) -->
    <div class="neu-raised rounded-2xl p-6 space-y-6">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary text-xl">timeline</span>
                <h3 class="font-headline text-base font-bold text-primary dark:text-surface">
                    Bitácora Cronológica de Intervenciones ({{ $incident->logs->count() }})
                </h3>
            </div>
        </div>

        <!-- Formulario para Asentar Nuevo Avance Técnico -->
        @if (!in_array($incident->status, ['resuelto', 'baja_definitiva', 'cancelado']))
            <form method="POST" action="{{ route('incidents.log', $incident) }}" class="neu-inset p-4 rounded-xl space-y-3">
                @csrf
                <span class="font-mono text-xs font-bold text-primary dark:text-surface block">
                    + Registrar Avance Técnico de Taller
                </span>

                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                    <div class="sm:col-span-8">
                        <input type="text" name="activity" required placeholder="Acción realizada (ej. Desarmado, limpieza de contactos, prueba de osciloscopio)..." class="w-full px-3 py-2 rounded-lg bg-surface border border-outline-variant/40 text-xs" />
                    </div>
                    <div class="sm:col-span-4">
                        <select name="status_after" class="w-full px-3 py-2 rounded-lg bg-surface border border-outline-variant/40 text-xs">
                            <option value="en_diagnostico">En Diagnóstico</option>
                            <option value="en_reparacion" selected>En Reparación</option>
                        </select>
                    </div>
                </div>

                <div class="flex gap-2">
                    <input type="text" name="notes" placeholder="Detalle técnico u observaciones adicionales..." class="flex-1 px-3 py-1.5 rounded-lg bg-surface border border-outline-variant/40 text-xs" />
                    <button type="submit" class="neu-btn-primary px-4 py-1.5 rounded-lg font-headline font-semibold text-xs cursor-pointer">
                        Asentar Avance
                    </button>
                </div>
            </form>
        @endif

        <!-- Lista de Eventos -->
        <div class="relative pl-6 space-y-4 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-outline-variant/40">
            @forelse ($incident->logs->sortByDesc('created_at') as $log)
                <div class="relative">
                    <span class="absolute -left-6 top-1 w-2.5 h-2.5 rounded-full bg-secondary ring-4 ring-surface"></span>
                    <div class="neu-inset p-3 rounded-xl font-body text-xs space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-headline font-bold text-primary dark:text-surface">{{ $log->activity }}</span>
                            <span class="font-mono text-[10px] text-outline">{{ $log->created_at->format('d/m/Y H:i:s') }}</span>
                        </div>
                        @if ($log->notes)
                            <p class="text-on-surface-variant text-[11px]">{{ $log->notes }}</p>
                        @endif
                        <div class="flex items-center gap-2 pt-1 font-mono text-[10px] text-outline">
                            <span>Técnico: <strong class="text-primary">{{ $log->technician->name ?? 'Operador' }}</strong></span>
                            @if ($log->status_after)
                                <span>• Estatus: <code class="px-1 py-0.2 rounded bg-surface text-secondary">{{ $log->status_after }}</code></span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-outline text-xs italic">No hay avances registrados aún en la bitácora del ticket.</p>
            @endforelse
        </div>
    </div>

</div>
@endsection


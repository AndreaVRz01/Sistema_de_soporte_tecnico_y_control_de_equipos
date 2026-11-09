@extends('layouts.app')

@section('title', 'Tickets y Servicios de Mantenimiento')

@section('header')
    <div>
        <h1 class="font-headline text-xl sm:text-2xl font-bold text-primary dark:text-surface tracking-tight">
            Levantamiento y Seguimiento de Tickets de Mantenimiento
        </h1>
        <p class="font-body text-xs text-on-surface-variant dark:text-inverse-primary mt-1">
            Diagnóstico especializado, órdenes de taller, bitácora de reparación y costos operativos de hardware.
        </p>
    </div>

    <div class="flex items-center gap-2">
        @unless (Auth::user()->isTecnico())
            <button type="button" onclick="openNewTicketModal()" class="neu-btn-primary px-4 py-2 rounded-xl font-headline font-semibold text-xs flex items-center gap-1.5 cursor-pointer">
                <span class="material-symbols-outlined text-base">build_circle</span>
                <span>+ Generar Ticket</span>
            </button>
        @endunless
    </div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Tarjetas de Conteo Rápido de Tickets -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <a href="{{ route('incidents.index') }}" class="neu-raised rounded-xl p-3 flex flex-col justify-between transition-transform active:scale-95 {{ !request('status') ? 'border-2 border-secondary' : '' }}">
            <span class="font-mono text-[10px] text-outline uppercase font-semibold">Total Tickets</span>
            <span class="font-headline text-xl font-bold text-primary dark:text-surface mt-1">{{ $counts['total'] }}</span>
        </a>
        <a href="{{ route('incidents.index', ['status' => 'taller']) }}" class="neu-raised rounded-xl p-3 flex flex-col justify-between transition-transform active:scale-95 {{ request('status') === 'taller' ? 'border-2 border-error' : '' }}">
            <span class="font-mono text-[10px] text-error uppercase font-semibold flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-error animate-live-dot"></span> En Taller
            </span>
            <span class="font-headline text-xl font-bold text-error mt-1">{{ $counts['taller'] }}</span>
        </a>
        <a href="{{ route('incidents.index', ['priority' => 'critica']) }}" class="neu-raised rounded-xl p-3 flex flex-col justify-between transition-transform active:scale-95 {{ request('priority') === 'critica' ? 'border-2 border-amber-500' : '' }}">
            <span class="font-mono text-[10px] text-amber-700 dark:text-amber-400 uppercase font-semibold flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Prioridad Crítica
            </span>
            <span class="font-headline text-xl font-bold text-amber-700 dark:text-amber-400 mt-1">{{ $counts['criticos'] }}</span>
        </a>
        <a href="{{ route('incidents.index', ['status' => 'resuelto']) }}" class="neu-raised rounded-xl p-3 flex flex-col justify-between transition-transform active:scale-95 {{ request('status') === 'resuelto' ? 'border-2 border-emerald-500' : '' }}">
            <span class="font-mono text-[10px] text-emerald-700 dark:text-emerald-400 uppercase font-semibold flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Resueltos
            </span>
            <span class="font-headline text-xl font-bold text-emerald-700 dark:text-emerald-400 mt-1">{{ $counts['resueltos'] }}</span>
        </a>
    </div>

    <!-- Barra de Filtros y Búsqueda -->
    <div class="neu-raised rounded-2xl p-4">
        <form method="GET" action="{{ route('incidents.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            
            <div class="sm:col-span-4 neu-inset rounded-xl flex items-center px-3 py-1.5">
                <span class="material-symbols-outlined text-secondary mr-2 text-lg">search</span>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Código #TK-..., serie, falla o activo..." 
                    class="w-full bg-transparent border-0 font-body text-xs text-on-surface focus:outline-none focus:ring-0 placeholder:text-outline p-0"
                />
            </div>

            <div class="sm:col-span-3">
                <select name="priority" onchange="this.form.submit()" class="neu-inset w-full px-3 py-1.5 rounded-xl font-body text-xs text-on-surface border-0 focus:ring-1 focus:ring-secondary">
                    <option value="">Todas las Prioridades</option>
                    <option value="critica" {{ request('priority') === 'critica' ? 'selected' : '' }}>Crítica (Atención Inmediata)</option>
                    <option value="alta" {{ request('priority') === 'alta' ? 'selected' : '' }}>Alta</option>
                    <option value="media" {{ request('priority') === 'media' ? 'selected' : '' }}>Media</option>
                    <option value="baja" {{ request('priority') === 'baja' ? 'selected' : '' }}>Baja</option>
                </select>
            </div>

            <div class="sm:col-span-3">
                <select name="status" onchange="this.form.submit()" class="neu-inset w-full px-3 py-1.5 rounded-xl font-body text-xs text-on-surface border-0 focus:ring-1 focus:ring-secondary">
                    <option value="">Todos los Estados</option>
                    <option value="taller" {{ request('status') === 'taller' ? 'selected' : '' }}>Activos en Taller</option>
                    <option value="reportado" {{ request('status') === 'reportado' ? 'selected' : '' }}>Reportado</option>
                    <option value="asignado" {{ request('status') === 'asignado' ? 'selected' : '' }}>Asignado a Técnico</option>
                    <option value="en_diagnostico" {{ request('status') === 'en_diagnostico' ? 'selected' : '' }}>En Diagnóstico</option>
                    <option value="en_reparacion" {{ request('status') === 'en_reparacion' ? 'selected' : '' }}>En Reparación</option>
                    <option value="resuelto" {{ request('status') === 'resuelto' ? 'selected' : '' }}>Resuelto</option>
                    <option value="baja_definitiva" {{ request('status') === 'baja_definitiva' ? 'selected' : '' }}>Baja Definitiva</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full neu-btn-primary py-1.5 px-3 rounded-xl font-headline text-xs font-semibold">
                    Filtrar
                </button>
                @if (request()->hasAny(['search', 'priority', 'status', 'type', 'technician_id']))
                    <a href="{{ route('incidents.index') }}" class="neu-btn-secondary p-1.5 rounded-xl text-outline hover:text-error" title="Limpiar filtros">
                        <span class="material-symbols-outlined text-base">filter_alt_off</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Lista de Tickets en Taller -->
    <div class="neu-raised rounded-2xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse font-body text-xs">
                <thead>
                    <tr class="border-b border-outline-variant/40 bg-surface-container-low dark:bg-primary-container/40">
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Ticket & Fecha</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Activo Afectado</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Falla / Asunto</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Prioridad</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Técnico Asignado</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Estatus</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($incidents as $ticket)
                        <tr class="hover:bg-surface-container/50 dark:hover:bg-primary/40 transition-colors">
                            <!-- Ticket & Fecha -->
                            <td class="p-3.5">
                                <a href="{{ route('incidents.show', $ticket) }}" class="font-mono font-bold text-primary dark:text-surface hover:text-secondary block">
                                    {{ $ticket->ticket_code }}
                                </a>
                                <span class="font-mono text-[10px] text-outline">
                                    {{ $ticket->reported_at->format('d/m/Y H:i') }}
                                </span>
                            </td>

                            <!-- Activo -->
                            <td class="p-3.5">
                                <span class="font-headline font-semibold text-primary dark:text-surface block">{{ $ticket->equipment->name ?? 'Equipo' }}</span>
                                <span class="font-mono text-[11px] text-secondary">
                                    {{ $ticket->equipment->inventory_code ?? 'ACT-000' }} (SN: {{ $ticket->equipment->serial_number ?? 'N/A' }})
                                </span>
                            </td>

                            <!-- Falla / Asunto -->
                            <td class="p-3.5 max-w-xs">
                                <p class="font-headline font-semibold text-xs text-primary dark:text-surface truncate">{{ $ticket->title }}</p>
                                <p class="font-body text-[11px] text-outline truncate">{{ $ticket->description }}</p>
                                <span class="inline-block mt-0.5 px-2 py-0.2 rounded font-mono text-[9px] bg-surface-container text-outline">
                                    {{ str_replace('_', ' ', strtoupper($ticket->type)) }}
                                </span>
                            </td>

                            <!-- Prioridad -->
                            <td class="p-3.5">
                                @if ($ticket->priority === 'critica')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-error-container text-on-error-container border border-error/40 font-mono text-[10px] font-bold uppercase animate-pulse">
                                        <span class="w-1.5 h-1.5 rounded-full bg-error"></span> Crítica
                                    </span>
                                @elseif ($ticket->priority === 'alta')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-300 font-mono text-[10px] font-bold uppercase">
                                        Alta
                                    </span>
                                @elseif ($ticket->priority === 'media')
                                    <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-800 border border-blue-200 font-mono text-[10px]">
                                        Media
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full bg-surface-container text-outline font-mono text-[10px]">
                                        Baja
                                    </span>
                                @endif
                            </td>

                            <!-- Técnico -->
                            <td class="p-3.5">
                                @if ($ticket->technician)
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-6 h-6 rounded-full bg-primary-container text-secondary flex items-center justify-center font-bold text-[10px]">
                                            {{ substr($ticket->technician->name, 0, 1) }}
                                        </div>
                                        <span class="font-mono text-xs text-primary dark:text-surface">{{ $ticket->technician->name }}</span>
                                    </div>
                                @else
                                    <span class="font-mono text-[11px] text-outline italic">Sin asignar</span>
                                @endif
                            </td>

                            <!-- Estatus -->
                            <td class="p-3.5">
                                @if (in_array($ticket->status, ['reportado', 'asignado', 'en_diagnostico', 'en_reparacion']))
                                    <span class="px-2.5 py-0.5 rounded-full bg-error-container text-on-error-container font-mono text-[10px] font-bold">
                                        {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                    </span>
                                @elseif ($ticket->status === 'resuelto')
                                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-300 font-mono text-[10px] font-bold">
                                        Resuelto
                                    </span>
                                @elseif ($ticket->status === 'baja_definitiva')
                                    <span class="px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-700 font-mono text-[10px]">
                                        Baja Definitiva
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full bg-surface-container text-outline font-mono text-[10px]">
                                        {{ ucfirst($ticket->status) }}
                                    </span>
                                @endif
                            </td>

                            <!-- Acciones -->
                            <td class="p-3.5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('incidents.show', $ticket) }}" class="p-1 rounded-lg hover:bg-surface-container text-on-surface-variant hover:text-primary transition-colors" title="Inspección de orden">
                                        <span class="material-symbols-outlined text-base">visibility</span>
                                    </a>

                                    @if (!in_array($ticket->status, ['resuelto', 'baja_definitiva', 'cancelado']))
                                        <button type="button" onclick="openResolveModal({{ $ticket->id }}, '{{ $ticket->ticket_code }}', '{{ $ticket->equipment->name ?? 'Equipo' }}')" class="neu-btn-secondary px-2 py-0.5 rounded-lg font-mono text-[10px] font-bold text-emerald-700 hover:text-emerald-900 transition-colors flex items-center gap-1">
                                            <span class="material-symbols-outlined text-xs">done_all</span>
                                            <span>Resolver</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-outline">
                                <span class="material-symbols-outlined text-3xl mb-1 block">build</span>
                                <p class="font-headline font-semibold text-sm">No existen tickets de soporte técnico con estos filtros.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($incidents->hasPages())
            <div class="p-3.5 border-t border-outline-variant/30 flex justify-between items-center">
                {{ $incidents->links() }}
            </div>
        @endif
    </div>

</div>

<!-- ========================================================================= -->
<!-- MODAL: LEVANTAR NUEVO TICKET DE MANTENIMIENTO                             -->
<!-- ========================================================================= -->
<div id="newTicketModal" class="fixed inset-0 z-50 bg-primary/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="neu-raised rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 border border-outline-variant/40 shadow-xl">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30 mb-4">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary text-xl">build_circle</span>
                <h3 class="font-headline text-lg font-bold text-primary dark:text-surface">Levantar Orden de Mantenimiento</h3>
            </div>
            <button type="button" onclick="closeNewTicketModal()" class="text-outline hover:text-primary">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form method="POST" action="{{ route('incidents.store') }}" class="space-y-4">
            @csrf

            <!-- Selección de Activo -->
            <div class="space-y-1">
                <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Activo Patrimonial con Avería o Falla *</label>
                <select name="equipment_id" required class="neu-inset w-full px-3 py-2 rounded-xl text-xs">
                    <option value="">Seleccione el activo que ingresará a taller...</option>
                    @foreach ($equipmentList as $eq)
                        <option value="{{ $eq->id }}">
                            {{ $eq->inventory_code }} - {{ $eq->name }} (SN: {{ $eq->serial_number }}) [Ubicación: {{ $eq->location }}]
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Título del Ticket -->
            <div class="space-y-1">
                <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Título / Resumen de la Falla *</label>
                <input type="text" name="title" required placeholder="Ej. Falla de display, conector USB-C flojo, sobrecalentamiento..." class="neu-inset w-full px-3 py-2 rounded-xl text-xs" />
            </div>

            <!-- Tipo & Prioridad -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Tipo de Servicio *</label>
                    <select name="type" required class="neu-inset w-full px-3 py-2 rounded-xl text-xs">
                        <option value="hardware">Hardware / Componente Físico</option>
                        <option value="software">Software / Sistema Operativo</option>
                        <option value="daño_fisico">Daño Físico / Golpe / Rotura</option>
                        <option value="desgaste_natural">Desgaste Natural</option>
                        <option value="perdida_accesorio">Pérdida de Accesorio</option>
                        <option value="mantenimiento_preventivo">Mantenimiento Preventivo Programado</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Nivel de Prioridad / Urgencia *</label>
                    <select name="priority" required class="neu-inset w-full px-3 py-2 rounded-xl text-xs">
                        <option value="media" selected>Media (Normal)</option>
                        <option value="baja">Baja</option>
                        <option value="alta">Alta</option>
                        <option value="critica">Crítica (Interrupción Operativa)</option>
                    </select>
                </div>
            </div>

            <!-- Técnico Asignado & Costo Estimado -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Técnico Especialista Asignado</label>
                    <select name="assigned_technician_id" class="neu-inset w-full px-3 py-2 rounded-xl text-xs">
                        <option value="">Dejar sin asignar temporalmente...</option>
                        @foreach ($technicians as $tech)
                            <option value="{{ $tech->id }}">{{ $tech->name }} ({{ $tech->career_or_department ?? 'Soporte' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Costo Estimado de Repuestos ($ MXN)</label>
                    <input type="number" step="0.01" name="estimated_cost" placeholder="0.00" class="neu-inset w-full px-3 py-2 rounded-xl text-xs" />
                </div>
            </div>

            <!-- Descripción Detallada -->
            <div class="space-y-1">
                <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Descripción Detallada del Síntoma o Alcance *</label>
                <textarea name="description" rows="3" required placeholder="Detalle las pruebas preliminares realizadas, códigos de error en pantalla o causas de la falla..." class="neu-inset w-full px-3 py-2 rounded-xl text-xs"></textarea>
            </div>

            <div class="pt-3 border-t border-outline-variant/30 flex justify-end gap-2">
                <button type="button" onclick="closeNewTicketModal()" class="neu-btn-secondary px-4 py-2 rounded-xl font-mono text-xs font-bold text-outline">
                    Cancelar
                </button>
                <button type="submit" class="neu-btn-primary px-5 py-2 rounded-xl font-headline font-semibold text-xs flex items-center gap-1.5 cursor-pointer">
                    <span class="material-symbols-outlined text-base">check</span>
                    <span>Ingresar Activo a Taller</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: RESOLVER / CERRAR TICKET DE MANTENIMIENTO                          -->
<!-- ========================================================================= -->
<div id="modalResolveTicket" class="fixed inset-0 z-50 bg-primary/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="neu-raised rounded-2xl w-full max-w-lg p-6 border border-outline-variant/40 shadow-xl">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30 mb-4">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-600 text-xl">done_all</span>
                <h3 class="font-headline text-lg font-bold text-primary dark:text-surface">
                    Dictaminar Solución: <span id="resolveModalCode" class="font-mono text-secondary"></span>
                </h3>
            </div>
            <button type="button" onclick="closeResolveModal()" class="text-outline hover:text-primary">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form id="resolveForm" method="POST" action="" class="space-y-4">
            @csrf

            <p class="font-body text-xs text-on-surface-variant" id="resolveModalEquip"></p>

            <div class="space-y-1">
                <label class="block font-mono text-xs font-bold text-primary">Diagnóstico Técnico Efectuado *</label>
                <textarea name="diagnostic" required rows="2" placeholder="Diagnóstico de causa raíz del fallo..." class="neu-inset w-full px-3 py-2 rounded-xl text-xs"></textarea>
            </div>

            <div class="space-y-1">
                <label class="block font-mono text-xs font-bold text-primary">Solución Aplicada / Piezas Reemplazadas *</label>
                <textarea name="solution_applied" required rows="2" placeholder="Acciones correctivas o mantenimiento ejecutado..." class="neu-inset w-full px-3 py-2 rounded-xl text-xs"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary">Costo Final Real ($ MXN)</label>
                    <input type="number" step="0.01" name="actual_cost" value="0.00" class="neu-inset w-full px-3 py-2 rounded-xl text-xs" />
                </div>
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary">Destino del Activo *</label>
                    <select name="equipment_status" required class="neu-inset w-full px-3 py-2 rounded-xl text-xs">
                        <option value="disponible">Reintegrar a Disponible</option>
                        <option value="baja">Baja Definitiva (Irreparable)</option>
                    </select>
                </div>
            </div>

            <div class="space-y-1">
                <label class="block font-mono text-xs font-bold text-primary">Condición Física Resultante *</label>
                <select name="physical_condition" required class="neu-inset w-full px-3 py-2 rounded-xl text-xs">
                    <option value="excelente">Excelente</option>
                    <option value="bueno" selected>Bueno</option>
                    <option value="regular">Regular</option>
                    <option value="malo">Malo</option>
                </select>
            </div>

            <div class="pt-3 border-t border-outline-variant/30 flex justify-end gap-2">
                <button type="button" onclick="closeResolveModal()" class="neu-btn-secondary px-4 py-2 rounded-xl font-mono text-xs font-bold text-outline">
                    Cancelar
                </button>
                <button type="submit" class="neu-btn-primary px-5 py-2 rounded-xl font-headline font-semibold text-xs flex items-center gap-1.5 cursor-pointer">
                    <span class="material-symbols-outlined text-base">check_circle</span>
                    <span>Cerrar Orden y Liberar</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openNewTicketModal() {
        document.getElementById('newTicketModal').classList.remove('hidden');
    }
    function closeNewTicketModal() {
        document.getElementById('newTicketModal').classList.add('hidden');
    }

    function openResolveModal(ticketId, ticketCode, equipName) {
        document.getElementById('resolveModalCode').textContent = ticketCode;
        document.getElementById('resolveModalEquip').textContent = `Activo: ${equipName}`;
        document.getElementById('resolveForm').action = `/tickets/${ticketId}/resolver`;
        document.getElementById('modalResolveTicket').classList.remove('hidden');
    }
    function closeResolveModal() {
        document.getElementById('modalResolveTicket').classList.add('hidden');
    }
</script>
@endsection


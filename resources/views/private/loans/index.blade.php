@extends('layouts.app')

@section('title', 'Control Detallado de Préstamos y Devoluciones')

@section('header')
    <div>
        <h1 class="font-headline text-xl sm:text-2xl font-bold text-primary dark:text-surface tracking-tight">
            Control Detallado de Préstamos y Devoluciones
        </h1>
        <p class="font-body text-xs text-on-surface-variant dark:text-inverse-primary mt-1">
            Gestión de ciclo de vida de actas, entregas en ventanilla, devoluciones con dictamen físico y alertas de vencimiento.
        </p>
    </div>

    <div class="flex items-center gap-2">
        @if (Auth::user()->role === \App\Models\User::ROLE_ENCARGADO_PRESTAMOS)
            <a href="{{ route('loans.borrowers.create') }}" class="neu-btn-secondary px-3.5 py-2 rounded-xl font-mono text-xs font-bold text-secondary flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base">person_add</span>
                <span>Registrar solicitante</span>
            </a>
        @endif
        <button type="button" onclick="openNewLoanModal()" class="neu-btn-primary px-4 py-2 rounded-xl font-headline font-semibold text-xs flex items-center gap-1.5 cursor-pointer">
            <span class="material-symbols-outlined text-base">add_circle</span>
            <span>+ Generar Préstamo</span>
        </button>
    </div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Tarjetas de Conteo Rápido de Préstamos -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <a href="{{ route('loans.index') }}" class="neu-raised rounded-xl p-3 flex flex-col justify-between transition-transform active:scale-95 {{ !request('status') ? 'border-2 border-secondary' : '' }}">
            <span class="font-mono text-[10px] text-outline uppercase font-semibold">Total Actas</span>
            <span class="font-headline text-xl font-bold text-primary dark:text-surface mt-1">{{ $counts['total'] }}</span>
        </a>
        <a href="{{ route('loans.index', ['status' => 'solicitado']) }}" class="neu-raised rounded-xl p-3 flex flex-col justify-between transition-transform active:scale-95 {{ request('status') === 'solicitado' ? 'border-2 border-secondary' : '' }}">
            <span class="font-mono text-[10px] text-secondary uppercase font-semibold flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span> Solicitados
            </span>
            <span class="font-headline text-xl font-bold text-secondary mt-1">{{ $counts['solicitados'] }}</span>
        </a>
        <a href="{{ route('loans.index', ['status' => 'entregado']) }}" class="neu-raised rounded-xl p-3 flex flex-col justify-between transition-transform active:scale-95 {{ request('status') === 'entregado' ? 'border-2 border-emerald-500' : '' }}">
            <span class="font-mono text-[10px] text-emerald-700 dark:text-emerald-400 uppercase font-semibold flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-live-dot"></span> Activos
            </span>
            <span class="font-headline text-xl font-bold text-emerald-700 dark:text-emerald-400 mt-1">{{ $counts['activos'] }}</span>
        </a>
        <a href="{{ route('loans.index', ['status' => 'vencido']) }}" class="neu-raised rounded-xl p-3 flex flex-col justify-between transition-transform active:scale-95 {{ request('status') === 'vencido' ? 'border-2 border-error' : '' }}">
            <span class="font-mono text-[10px] text-error uppercase font-semibold flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-error"></span> Vencidos
            </span>
            <span class="font-headline text-xl font-bold text-error mt-1">{{ $counts['vencidos'] }}</span>
        </a>
        <a href="{{ route('loans.index', ['status' => 'devuelto']) }}" class="neu-raised rounded-xl p-3 flex flex-col justify-between transition-transform active:scale-95 {{ request('status') === 'devuelto' ? 'border-2 border-outline' : '' }}">
            <span class="font-mono text-[10px] text-outline uppercase font-semibold">Devueltos</span>
            <span class="font-headline text-xl font-bold text-outline mt-1">{{ $counts['devueltos'] }}</span>
        </a>
    </div>

    <!-- Filtros de Búsqueda de Préstamos -->
    <div class="neu-raised rounded-2xl p-4">
        <form method="GET" action="{{ route('loans.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <div class="sm:col-span-6 neu-inset rounded-xl flex items-center px-3 py-1.5">
                <span class="material-symbols-outlined text-secondary mr-2 text-lg">search</span>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Buscar por Folio (PR-2026-...), custodio o matrícula..." 
                    class="w-full bg-transparent border-0 font-body text-xs text-on-surface focus:outline-none focus:ring-0 placeholder:text-outline p-0"
                />
            </div>

            <div class="sm:col-span-4">
                <select name="status" onchange="this.form.submit()" class="neu-inset w-full px-3 py-1.5 rounded-xl font-body text-xs text-on-surface border-0 focus:ring-1 focus:ring-secondary">
                    <option value="">Todos los Estados</option>
                    <option value="solicitado" {{ request('status') === 'solicitado' ? 'selected' : '' }}>Solicitado (Pendiente)</option>
                    <option value="aprobado" {{ request('status') === 'aprobado' ? 'selected' : '' }}>Aprobado (Por Entregar)</option>
                    <option value="entregado" {{ request('status') === 'entregado' ? 'selected' : '' }}>Entregado (Activo)</option>
                    <option value="vencido" {{ request('status') === 'vencido' ? 'selected' : '' }}>Vencido (Retraso)</option>
                    <option value="devuelto" {{ request('status') === 'devuelto' ? 'selected' : '' }}>Devuelto Conforme</option>
                    <option value="devuelto_con_observacion" {{ request('status') === 'devuelto_con_observacion' ? 'selected' : '' }}>Devuelto con Observación</option>
                    <option value="rechazado" {{ request('status') === 'rechazado' ? 'selected' : '' }}>Rechazado</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full neu-btn-primary py-1.5 px-3 rounded-xl font-headline text-xs font-semibold">
                    Filtrar
                </button>
                @if (request()->hasAny(['search', 'status', 'user_id']))
                    <a href="{{ route('loans.index') }}" class="neu-btn-secondary p-1.5 rounded-xl text-outline hover:text-error" title="Limpiar filtros">
                        <span class="material-symbols-outlined text-base">filter_alt_off</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla Detallada de Préstamos -->
    <div class="neu-raised rounded-2xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse font-body text-xs">
                <thead>
                    <tr class="border-b border-outline-variant/40 bg-surface-container-low dark:bg-primary-container/40">
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Folio / Acta</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Custodio (Usuario)</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Destino / Uso</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Items Vinculados</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Fecha Límite</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Estado</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($loans as $loan)
                        @php
                            $isOverdue = $loan->isOverdue();
                        @endphp
                        <tr class="hover:bg-surface-container/50 dark:hover:bg-primary/40 transition-colors {{ $isOverdue ? 'bg-error-container/10' : '' }}">
                            <!-- Folio -->
                            <td class="p-3.5">
                                <a href="{{ route('loans.show', $loan) }}" class="font-mono font-bold text-primary dark:text-surface hover:text-secondary block">
                                    {{ $loan->folio }}
                                </a>
                                <span class="font-mono text-[10px] text-outline">
                                    {{ $loan->request_date->format('d/m/Y H:i') }}
                                </span>
                            </td>

                            <!-- Custodio -->
                            <td class="p-3.5">
                                <p class="font-headline font-semibold text-primary dark:text-surface">{{ $loan->user->name ?? 'Usuario Desconocido' }}</p>
                                <p class="font-mono text-[11px] text-outline">{{ $loan->user->institutional_id ?? 'N/A' }} • {{ ucfirst($loan->user->user_type ?? 'Usuario') }}</p>
                            </td>

                            <!-- Destino / Ubicación -->
                            <td class="p-3.5">
                                <p class="font-body text-xs text-primary dark:text-surface line-clamp-1">{{ $loan->location_of_use ?? 'Campus Central' }}</p>
                                <p class="font-body text-[11px] text-outline line-clamp-1">{{ $loan->purpose }}</p>
                            </td>

                            <!-- Items Vinculados -->
                            <td class="p-3.5">
                                <div class="flex flex-col gap-1">
                                    @foreach ($loan->items->take(2) as $item)
                                        <span class="inline-flex items-center gap-1 font-mono text-[11px] text-primary dark:text-surface">
                                            <span class="material-symbols-outlined text-xs text-secondary">devices</span>
                                            {{ $item->equipment->inventory_code ?? 'ACT-000' }} ({{ $item->equipment->name ?? 'Equipo' }})
                                        </span>
                                    @endforeach
                                    @if ($loan->items->count() > 2)
                                        <span class="font-mono text-[10px] text-outline">+{{ $loan->items->count() - 2 }} más...</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Fecha Límite -->
                            <td class="p-3.5">
                                <span class="font-mono text-[11px] {{ $isOverdue ? 'text-error font-bold' : 'text-on-surface-variant' }}">
                                    {{ $loan->expected_return_date->format('d/m/Y H:i') }}
                                </span>
                                @if ($isOverdue)
                                    <span class="block font-mono text-[9px] text-error font-bold uppercase">¡Vencido!</span>
                                @endif
                            </td>

                            <!-- Estado -->
                            <td class="p-3.5">
                                @if ($loan->status === 'solicitado')
                                    <span class="px-2.5 py-0.5 rounded-full bg-surface-container text-secondary border border-secondary/40 font-mono text-[10px] font-bold">
                                        Solicitado
                                    </span>
                                @elseif ($loan->status === 'aprobado')
                                    <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-800 border border-blue-300 font-mono text-[10px] font-bold">
                                        Aprobado
                                    </span>
                                @elseif ($loan->status === 'entregado')
                                    @if ($isOverdue)
                                        <span class="px-2.5 py-0.5 rounded-full bg-error-container text-on-error-container border border-error/40 font-mono text-[10px] font-bold animate-pulse">
                                            Activo (Vencido)
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-300 font-mono text-[10px] font-bold">
                                            Entregado / Activo
                                        </span>
                                    @endif
                                @elseif ($loan->status === 'devuelto')
                                    <span class="px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-700 border border-gray-300 font-mono text-[10px]">
                                        Devuelto Conforme
                                    </span>
                                @elseif ($loan->status === 'devuelto_con_observacion')
                                    <span class="px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-300 font-mono text-[10px] font-bold">
                                        Devuelto con Obs.
                                    </span>
                                @elseif ($loan->status === 'rechazado')
                                    <span class="px-2.5 py-0.5 rounded-full bg-error-container text-on-error-container font-mono text-[10px]">
                                        Rechazado
                                    </span>
                                @endif
                            </td>

                            <!-- Acciones -->
                            <td class="p-3.5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <!-- Ver Acta -->
                                    <a href="{{ route('loans.show', $loan) }}" class="p-1 rounded-lg hover:bg-surface-container text-on-surface-variant hover:text-primary transition-colors" title="Ver acta de préstamo">
                                        <span class="material-symbols-outlined text-base">visibility</span>
                                    </a>

                                    <!-- Si está solicitado: Aprobar o Rechazar -->
                                    @if ($loan->status === 'solicitado')
                                        <form method="POST" action="{{ route('loans.approve', $loan) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="p-1 rounded-lg hover:bg-emerald-50 text-emerald-700 transition-colors" title="Autorizar préstamo">
                                                <span class="material-symbols-outlined text-base">check_circle</span>
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Si está aprobado: Despachar / Entregar -->
                                    @if (in_array($loan->status, ['solicitado', 'aprobado']))
                                        <form method="POST" action="{{ route('loans.dispatch', $loan) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="p-1 rounded-lg hover:bg-blue-50 text-blue-700 transition-colors" title="Entregar activos físicamente">
                                                <span class="material-symbols-outlined text-base">handshake</span>
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Si está entregado: Recibir devolución -->
                                    @if ($loan->status === 'entregado')
                                        <button type="button" onclick="openReturnModal({{ $loan->id }}, '{{ $loan->folio }}', {{ json_encode($loan->items) }})" class="neu-btn-secondary px-2 py-0.5 rounded-lg font-mono text-[10px] font-bold text-secondary hover:text-primary transition-colors flex items-center gap-1">
                                            <span class="material-symbols-outlined text-xs">assignment_return</span>
                                            <span>Devolver</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-outline">
                                <span class="material-symbols-outlined text-3xl mb-1 block">handshake</span>
                                <p class="font-headline font-semibold text-sm">No hay registros de préstamos que coincidan con la búsqueda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($loans->hasPages())
            <div class="p-3.5 border-t border-outline-variant/30 flex justify-between items-center">
                {{ $loans->links() }}
            </div>
        @endif
    </div>

</div>

<!-- ========================================================================= -->
<!-- MODAL: REGISTRAR NUEVO PRÉSTAMO DE ACTIVOS                                -->
<!-- ========================================================================= -->
<div id="newLoanModal" class="fixed inset-0 z-50 bg-primary/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="neu-raised rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 border border-outline-variant/40 shadow-xl">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30 mb-4">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary text-xl">handshake</span>
                <h3 class="font-headline text-lg font-bold text-primary dark:text-surface">Registrar Nuevo Préstamo de Activos</h3>
            </div>
            <button type="button" onclick="closeNewLoanModal()" class="text-outline hover:text-primary">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form method="POST" action="{{ route('loans.store') }}" class="space-y-4">
            @csrf

            <!-- Custodio / Solicitante -->
            <div class="space-y-1">
                <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Custodio Solicitante (Alumno / Docente) *</label>
                <select name="user_id" required class="neu-inset w-full px-3 py-2 rounded-xl text-xs">
                    <option value="">Seleccione el usuario que recibirá el activo...</option>
                    @foreach ($eligibleUsers as $usr)
                        <option value="{{ $usr->id }}" {{ (string) $selectedBorrowerId === (string) $usr->id ? 'selected' : '' }}>
                            {{ $usr->name }} ({{ $usr->institutional_id }}) - {{ ucfirst($usr->user_type) }} [{{ $usr->career_or_department ?? 'General' }}]
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Selección de Equipos Disponibles -->
            <div class="space-y-1">
                <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Activos Disponibles para Asignar *</label>
                <select name="equipment_ids[]" multiple required class="neu-inset w-full px-3 py-2 rounded-xl text-xs h-32">
                    @foreach ($availableEquipments as $eq)
                        <option value="{{ $eq->id }}">
                            {{ $eq->inventory_code }} - {{ $eq->name }} (SN: {{ $eq->serial_number }}) [{{ $eq->location }}]
                        </option>
                    @endforeach
                </select>
                <p class="font-mono text-[10px] text-outline">Mantenga presionada la tecla Ctrl para seleccionar más de un activo.</p>
            </div>

            <!-- Propósito & Ubicación -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Motivo / Propósito del Préstamo *</label>
                    <input type="text" name="purpose" required placeholder="Ej. Práctica de robótica / Evaluación final" class="neu-inset w-full px-3 py-2 rounded-xl text-xs" />
                </div>
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Ubicación de Uso *</label>
                    <input type="text" name="location_of_use" required placeholder="Ej. Aula Magna / Lab Sistemas" class="neu-inset w-full px-3 py-2 rounded-xl text-xs" />
                </div>
            </div>

            <!-- Fecha Límite de Devolución -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Fecha & Hora Límite de Retorno *</label>
                    <input type="datetime-local" name="expected_return_date" required value="{{ now()->addDays(2)->format('Y-m-d\TH:i') }}" class="neu-inset w-full px-3 py-2 rounded-xl text-xs" />
                </div>
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary dark:text-surface">Accesorios Incluidos</label>
                    <input type="text" name="accessories_included" placeholder="Cargador original, cable HDMI, maletín..." class="neu-inset w-full px-3 py-2 rounded-xl text-xs" />
                </div>
            </div>

            <!-- Despacho Inmediato -->
            <div class="p-3 rounded-xl neu-inset bg-surface-container-low flex items-center gap-2">
                <input type="checkbox" name="immediate_dispatch" value="1" id="imm_dispatch" class="w-4 h-4 rounded text-primary-container focus:ring-secondary cursor-pointer" checked />
                <label for="imm_dispatch" class="font-body text-xs text-on-surface cursor-pointer">
                    <span class="font-bold">Despacho Inmediato en Ventanilla:</span> Marcar los activos como prestados y generar acta en estado entregado inmediatamente.
                </label>
            </div>

            <div class="pt-3 border-t border-outline-variant/30 flex justify-end gap-2">
                <button type="button" onclick="closeNewLoanModal()" class="neu-btn-secondary px-4 py-2 rounded-xl font-mono text-xs font-bold text-outline">
                    Cancelar
                </button>
                <button type="submit" class="neu-btn-primary px-5 py-2 rounded-xl font-headline font-semibold text-xs flex items-center gap-1.5 cursor-pointer">
                    <span class="material-symbols-outlined text-base">check</span>
                    <span>Emitir Acta de Préstamo</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: RECEPCIÓN Y DEVOLUCIÓN DE ACTIVOS                                  -->
<!-- ========================================================================= -->
<div id="modalReturnLoan" class="fixed inset-0 z-50 bg-primary/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="neu-raised rounded-2xl w-full max-w-xl max-h-[90vh] overflow-y-auto p-6 border border-outline-variant/40 shadow-xl">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30 mb-4">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary text-xl">assignment_return</span>
                <h3 class="font-headline text-lg font-bold text-primary dark:text-surface">
                    Recepción Física de Préstamo: <span id="returnModalFolio" class="font-mono text-secondary"></span>
                </h3>
            </div>
            <button type="button" onclick="closeReturnModal()" class="text-outline hover:text-primary">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form id="returnForm" method="POST" action="" class="space-y-4">
            @csrf

            <p class="font-body text-xs text-on-surface-variant">
                Evalúe las condiciones físicas y estéticas con las que retorna cada activo entregado en el acta:
            </p>

            <div id="returnItemsContainer" class="space-y-3">
                <!-- Se llena dinámicamente con JS -->
            </div>

            <div class="pt-3 border-t border-outline-variant/30 flex justify-end gap-2">
                <button type="button" onclick="closeReturnModal()" class="neu-btn-secondary px-4 py-2 rounded-xl font-mono text-xs font-bold text-outline">
                    Cancelar
                </button>
                <button type="submit" class="neu-btn-primary px-5 py-2 rounded-xl font-headline font-semibold text-xs flex items-center gap-1.5 cursor-pointer">
                    <span class="material-symbols-outlined text-base">task_alt</span>
                    <span>Confirmar Recepción</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openNewLoanModal() {
        document.getElementById('newLoanModal').classList.remove('hidden');
    }
    function closeNewLoanModal() {
        document.getElementById('newLoanModal').classList.add('hidden');
    }

    function openReturnModal(loanId, folio, items) {
        document.getElementById('returnModalFolio').textContent = folio;
        document.getElementById('returnForm').action = `/prestamos/${loanId}/devolucion`;

        const container = document.getElementById('returnItemsContainer');
        container.innerHTML = '';

        items.forEach((item, index) => {
            const card = document.createElement('div');
            card.className = 'neu-inset p-3 rounded-xl space-y-2';
            card.innerHTML = `
                <div class="flex justify-between items-center">
                    <span class="font-mono text-xs font-bold text-primary">Item #${index + 1}: ${item.equipment ? item.equipment.name : 'Activo'}</span>
                    <span class="font-mono text-[11px] text-outline">${item.equipment ? item.equipment.inventory_code : ''}</span>
                </div>
                <input type="hidden" name="items[${index}][loan_item_id]" value="${item.id}" />
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <div>
                        <label class="block font-mono text-[10px] text-outline font-bold">Condición de Retorno</label>
                        <select name="items[${index}][condition_on_return]" class="w-full px-2 py-1 rounded bg-surface border border-outline-variant/40 text-xs">
                            <option value="excelente">Excelente Estado</option>
                            <option value="bueno" selected>Bueno / Normal</option>
                            <option value="regular">Regular / Desgaste leve</option>
                            <option value="dañado">Dañado / Con Avería (Abre Ticket)</option>
                            <option value="incompleto">Incompleto / Faltan Piezas</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-mono text-[10px] text-outline font-bold">Observación Técnica</label>
                        <input type="text" name="items[${index}][notes_on_return]" placeholder="Sin novedad o describir fallo..." class="w-full px-2 py-1 rounded bg-surface border border-outline-variant/40 text-xs" />
                    </div>
                </div>
            `;
            container.appendChild(card);
        });

        document.getElementById('modalReturnLoan').classList.remove('hidden');
    }

    function closeReturnModal() {
        document.getElementById('modalReturnLoan').classList.add('hidden');
    }
</script>
@endsection


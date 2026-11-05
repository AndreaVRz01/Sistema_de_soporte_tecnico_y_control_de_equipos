@extends('layouts.app')

@section('title', 'Control de Sanciones Disciplinarias')

@section('header')
    <div>
        <h1 class="font-headline text-xl sm:text-2xl font-bold text-primary dark:text-surface tracking-tight">
            Control de Sanciones Disciplinarias & Bloqueos
        </h1>
        <p class="font-body text-xs text-on-surface-variant dark:text-inverse-primary mt-1">
            Gestión de suspensiones preventivas por retraso en devoluciones, cobros de reparación por daños físicos y penalizaciones.
        </p>
    </div>

    <div class="flex items-center gap-2">
        <button type="button" onclick="openCreateSanctionModal()" class="neu-btn-primary px-4 py-2 rounded-xl font-headline font-semibold text-xs flex items-center gap-1.5 cursor-pointer">
            <span class="material-symbols-outlined text-base">gavel</span>
            <span>+ Imponer Sanción</span>
        </button>
    </div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Tarjetas de Conteo de Sanciones -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <a href="{{ route('sanctions.index') }}" class="neu-raised rounded-xl p-3 flex flex-col justify-between transition-transform active:scale-95 {{ !request('status') ? 'border-2 border-secondary' : '' }}">
            <span class="font-mono text-[10px] text-outline uppercase font-semibold">Total Expedientes</span>
            <span class="font-headline text-xl font-bold text-primary dark:text-surface mt-1">{{ $counts['total'] }}</span>
        </a>
        <a href="{{ route('sanctions.index', ['status' => 'activa']) }}" class="neu-raised rounded-xl p-3 flex flex-col justify-between transition-transform active:scale-95 {{ request('status') === 'activa' ? 'border-2 border-error' : '' }}">
            <span class="font-mono text-[10px] text-error uppercase font-semibold flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-error animate-live-dot"></span> Sanciones Activas (Inhabilitados)
            </span>
            <span class="font-headline text-xl font-bold text-error mt-1">{{ $counts['activas'] }}</span>
        </a>
        <a href="{{ route('sanctions.index', ['status' => 'cumplida']) }}" class="neu-raised rounded-xl p-3 flex flex-col justify-between transition-transform active:scale-95 {{ request('status') === 'cumplida' ? 'border-2 border-emerald-500' : '' }}">
            <span class="font-mono text-[10px] text-emerald-700 dark:text-emerald-400 uppercase font-semibold flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Cumplidas / Levantadas
            </span>
            <span class="font-headline text-xl font-bold text-emerald-700 dark:text-emerald-400 mt-1">{{ $counts['cumplidas'] }}</span>
        </a>
    </div>

    <!-- Tabla de Sanciones -->
    <div class="neu-raised rounded-2xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse font-body text-xs">
                <thead>
                    <tr class="border-b border-outline-variant/40 bg-surface-container-low dark:bg-primary-container/40">
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Usuario Sancionado</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Tipo de Medida</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Motivo / Causa</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Periodo de Suspensión</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Monto por Daño</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Estatus</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($sanctions as $sanc)
                        <tr class="hover:bg-surface-container/50 dark:hover:bg-primary/40 transition-colors">
                            <!-- Usuario -->
                            <td class="p-3.5">
                                <span class="font-headline font-bold text-primary dark:text-surface block">{{ $sanc->user->name ?? 'Usuario' }}</span>
                                <span class="font-mono text-[11px] text-outline">{{ $sanc->user->institutional_id ?? 'N/A' }} • {{ ucfirst($sanc->user->user_type ?? 'Usuario') }}</span>
                            </td>

                            <!-- Tipo de Medida -->
                            <td class="p-3.5">
                                <span class="px-2.5 py-0.5 rounded-full font-mono text-[10px] font-bold uppercase
                                    @if ($sanc->sanction_type === 'bloqueo_definitivo') bg-error-container text-on-error-container
                                    @elseif ($sanc->sanction_type === 'suspension_temporal') bg-amber-100 text-amber-900
                                    @elseif ($sanc->sanction_type === 'cobro_reparacion') bg-purple-100 text-purple-900
                                    @else bg-surface-container text-primary @endif">
                                    {{ str_replace('_', ' ', $sanc->sanction_type) }}
                                </span>
                            </td>

                            <!-- Motivo -->
                            <td class="p-3.5 max-w-xs">
                                <p class="text-on-surface line-clamp-2">{{ $sanc->reason }}</p>
                            </td>

                            <!-- Periodo -->
                            <td class="p-3.5 font-mono text-[11px] text-on-surface-variant">
                                <span>Del: {{ $sanc->start_date->format('d/m/Y') }}</span>
                                <span class="block text-outline">Al: {{ $sanc->end_date ? $sanc->end_date->format('d/m/Y') : 'Indefinido' }}</span>
                            </td>

                            <!-- Monto -->
                            <td class="p-3.5 font-mono text-xs">
                                @if ($sanc->amount_to_pay > 0)
                                    <span class="font-bold text-primary">${{ number_format($sanc->amount_to_pay, 2) }}</span>
                                    <span class="block text-[10px] {{ $sanc->is_paid ? 'text-emerald-700 font-bold' : 'text-error' }}">
                                        {{ $sanc->is_paid ? 'Pagado' : 'Pendiente de Pago' }}
                                    </span>
                                @else
                                    <span class="text-outline">N/A</span>
                                @endif
                            </td>

                            <!-- Estatus -->
                            <td class="p-3.5">
                                @if ($sanc->status === 'activa')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-error-container text-on-error-container font-mono text-[10px] font-bold uppercase">
                                        <span class="w-1.5 h-1.5 rounded-full bg-error"></span> Activa
                                    </span>
                                @elseif ($sanc->status === 'cumplida')
                                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-300 font-mono text-[10px] font-bold">
                                        Cumplida
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-700 font-mono text-[10px]">
                                        Cancelada
                                    </span>
                                @endif
                            </td>

                            <!-- Acciones -->
                            <td class="p-3.5 text-right">
                                @if ($sanc->status === 'activa')
                                    <button type="button" onclick="openResolveSanctionModal({{ $sanc->id }}, '{{ $sanc->user->name ?? 'Usuario' }}', {{ $sanc->amount_to_pay ?? 0 }})" class="neu-btn-secondary px-2.5 py-1 rounded-lg font-mono text-[10px] font-bold text-emerald-700 hover:text-emerald-900 transition-colors">
                                        Levantar Sanción
                                    </button>
                                @else
                                    <span class="font-mono text-[10px] text-outline">Finalizado</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-outline">
                                <span class="material-symbols-outlined text-3xl mb-1 block">gavel</span>
                                <p class="font-headline font-semibold text-sm">No existen sanciones registradas en este momento.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($sanctions->hasPages())
            <div class="p-3.5 border-t border-outline-variant/30 flex justify-between items-center">
                {{ $sanctions->links() }}
            </div>
        @endif
    </div>

</div>

<!-- ========================================================================= -->
<!-- MODAL: REGISTRAR NUEVA SANCIÓN                                            -->
<!-- ========================================================================= -->
<div id="modalCreateSanction" class="fixed inset-0 z-50 bg-primary/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="neu-raised rounded-2xl w-full max-w-lg p-6 border border-outline-variant/40 shadow-xl">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30 mb-4">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-error text-xl">gavel</span>
                <h3 class="font-headline text-lg font-bold text-primary dark:text-surface">Imponer Medida Disciplinaria</h3>
            </div>
            <button type="button" onclick="closeCreateSanctionModal()" class="text-outline hover:text-primary">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form method="POST" action="{{ route('sanctions.store') }}" class="space-y-4">
            @csrf

            <div class="space-y-1">
                <label class="block font-mono text-xs font-bold text-primary">Usuario Infractor *</label>
                <select name="user_id" required class="neu-inset w-full px-3 py-2 rounded-xl text-xs">
                    <option value="">Seleccione al alumno o docente...</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->institutional_id }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary">Tipo de Medida *</label>
                    <select name="sanction_type" required class="neu-inset w-full px-3 py-2 rounded-xl text-xs">
                        <option value="suspension_temporal">Suspensión Temporal</option>
                        <option value="cobro_reparacion">Cobro por Daño / Pérdida</option>
                        <option value="amonestacion_escrita">Amonestación Escrita</option>
                        <option value="bloqueo_definitivo">Bloqueo Definitivo</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary">Monto a Pagar ($ MXN)</label>
                    <input type="number" step="0.01" name="amount_to_pay" placeholder="0.00" class="neu-inset w-full px-3 py-2 rounded-xl text-xs" />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary">Fecha Inicio *</label>
                    <input type="date" name="start_date" value="{{ date('Y-m-d') }}" required class="neu-inset w-full px-3 py-2 rounded-xl text-xs" />
                </div>
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary">Fecha Fin Suspensión</label>
                    <input type="date" name="end_date" value="{{ date('Y-m-d', strtotime('+7 days')) }}" class="neu-inset w-full px-3 py-2 rounded-xl text-xs" />
                </div>
            </div>

            <div class="space-y-1">
                <label class="block font-mono text-xs font-bold text-primary">Motivo Detallado de la Sanción *</label>
                <textarea name="reason" required rows="3" placeholder="Retraso no justificado mayor a 48h, pantalla rota al devolver, etc..." class="neu-inset w-full px-3 py-2 rounded-xl text-xs"></textarea>
            </div>

            <div class="pt-3 border-t border-outline-variant/30 flex justify-end gap-2">
                <button type="button" onclick="closeCreateSanctionModal()" class="neu-btn-secondary px-4 py-2 rounded-xl font-mono text-xs font-bold text-outline">
                    Cancelar
                </button>
                <button type="submit" class="neu-btn-primary px-5 py-2 rounded-xl font-headline font-semibold text-xs flex items-center gap-1.5 cursor-pointer">
                    <span class="material-symbols-outlined text-base">check</span>
                    <span>Aplicar Sanción</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: LEVANTAR / CUMPLIR SANCIÓN                                         -->
<!-- ========================================================================= -->
<div id="modalResolveSanction" class="fixed inset-0 z-50 bg-primary/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="neu-raised rounded-2xl w-full max-w-md p-6 border border-outline-variant/40 shadow-xl">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30 mb-4">
            <h3 class="font-headline text-lg font-bold text-primary dark:text-surface">
                Levantamiento de Sanción
            </h3>
            <button type="button" onclick="closeResolveSanctionModal()" class="text-outline hover:text-primary">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form id="resolveSanctionForm" method="POST" action="" class="space-y-4">
            @csrf

            <p class="font-body text-xs text-on-surface-variant">
                Custodio: <strong id="resolveSanctionUserName" class="text-primary"></strong>
            </p>

            <div class="space-y-1">
                <label class="block font-mono text-xs font-bold text-primary">Resolución *</label>
                <select name="status" required class="neu-inset w-full px-3 py-2 rounded-xl text-xs">
                    <option value="cumplida">Sanción Cumplida (Reactivar Usuario)</option>
                    <option value="cancelada">Cancelada / Indulto Justificado</option>
                </select>
            </div>

            <div class="p-3 rounded-xl neu-inset bg-surface-container-low flex items-center gap-2" id="paidCheckboxGroup">
                <input type="checkbox" name="is_paid" value="1" id="is_paid_cb" class="w-4 h-4 rounded text-primary-container focus:ring-secondary" />
                <label for="is_paid_cb" class="font-body text-xs text-on-surface">
                    Marcar monto por reposición/daño como <strong>Liquidado / Pagado</strong>
                </label>
            </div>

            <div class="space-y-1">
                <label class="block font-mono text-xs font-bold text-primary">Notas del Levantamiento *</label>
                <textarea name="resolution_notes" required rows="2" placeholder="Cumplió con los días de suspensión o presentó comprobante de pago..." class="neu-inset w-full px-3 py-2 rounded-xl text-xs"></textarea>
            </div>

            <div class="pt-3 border-t border-outline-variant/30 flex justify-end gap-2">
                <button type="button" onclick="closeResolveSanctionModal()" class="neu-btn-secondary px-4 py-2 rounded-xl font-mono text-xs font-bold text-outline">
                    Cancelar
                </button>
                <button type="submit" class="neu-btn-primary px-5 py-2 rounded-xl font-headline font-semibold text-xs flex items-center gap-1.5 cursor-pointer">
                    <span class="material-symbols-outlined text-base">check_circle</span>
                    <span>Levantar Sanción</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateSanctionModal() {
        document.getElementById('modalCreateSanction').classList.remove('hidden');
    }
    function closeCreateSanctionModal() {
        document.getElementById('modalCreateSanction').classList.add('hidden');
    }

    function openResolveSanctionModal(sanctionId, userName, amount) {
        document.getElementById('resolveSanctionUserName').textContent = userName;
        document.getElementById('resolveSanctionForm').action = `/sanciones/${sanctionId}/resolver`;
        document.getElementById('modalResolveSanction').classList.remove('hidden');
    }
    function closeResolveSanctionModal() {
        document.getElementById('modalResolveSanction').classList.add('hidden');
    }
</script>
@endsection


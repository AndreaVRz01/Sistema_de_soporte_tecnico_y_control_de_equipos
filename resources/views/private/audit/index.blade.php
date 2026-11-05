@extends('layouts.app')

@section('title', 'Bitácora & Auditoría Forense')

@section('header')
    <div>
        <h1 class="font-headline text-xl sm:text-2xl font-bold text-primary dark:text-surface tracking-tight">
            Bitácora & Auditoría Forense de Movimientos
        </h1>
        <p class="font-body text-xs text-on-surface-variant dark:text-inverse-primary mt-1">
            Trazabilidad relacional en tiempo real, registro inmutable de operaciones, diffs de estado y evaluación de riesgo.
        </p>
    </div>

    <div class="flex items-center gap-2">
        <button type="button" onclick="openExportModal()" class="neu-btn-primary px-4 py-2 rounded-xl font-headline font-semibold text-xs flex items-center gap-1.5 cursor-pointer">
            <span class="material-symbols-outlined text-base">file_download</span>
            <span>Exportación Forense Avanzada</span>
        </button>
    </div>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Tarjetas de Métricas de Auditoría -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <a href="{{ route('audit.index') }}" class="neu-raised rounded-xl p-3 flex flex-col justify-between transition-transform active:scale-95 {{ !request('risk_level') ? 'border-2 border-secondary' : '' }}">
            <span class="font-mono text-[10px] text-outline uppercase font-semibold">Total Eventos</span>
            <span class="font-headline text-xl font-bold text-primary dark:text-surface mt-1">{{ $counts['total'] }}</span>
        </a>
        <a href="{{ route('audit.index', ['risk_level' => 'critico']) }}" class="neu-raised rounded-xl p-3 flex flex-col justify-between transition-transform active:scale-95 {{ request('risk_level') === 'critico' ? 'border-2 border-error' : '' }}">
            <span class="font-mono text-[10px] text-error uppercase font-semibold flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-error animate-live-dot"></span> Eventos Críticos
            </span>
            <span class="font-headline text-xl font-bold text-error mt-1">{{ $counts['critico'] }}</span>
        </a>
        <a href="{{ route('audit.index', ['risk_level' => 'advertencia']) }}" class="neu-raised rounded-xl p-3 flex flex-col justify-between transition-transform active:scale-95 {{ request('risk_level') === 'advertencia' ? 'border-2 border-amber-500' : '' }}">
            <span class="font-mono text-[10px] text-amber-700 dark:text-amber-400 uppercase font-semibold flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Advertencias
            </span>
            <span class="font-headline text-xl font-bold text-amber-700 dark:text-amber-400 mt-1">{{ $counts['advertencia'] }}</span>
        </a>
        <a href="{{ route('audit.index', ['risk_level' => 'normal']) }}" class="neu-raised rounded-xl p-3 flex flex-col justify-between transition-transform active:scale-95 {{ request('risk_level') === 'normal' ? 'border-2 border-emerald-500' : '' }}">
            <span class="font-mono text-[10px] text-emerald-700 dark:text-emerald-400 uppercase font-semibold flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Operaciones Normales
            </span>
            <span class="font-headline text-xl font-bold text-emerald-700 dark:text-emerald-400 mt-1">{{ $counts['normal'] }}</span>
        </a>
    </div>

    <!-- Barra de Búsqueda Multifactorial -->
    <div class="neu-raised rounded-2xl p-4">
        <form method="GET" action="{{ route('audit.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            
            <div class="sm:col-span-4 neu-inset rounded-xl flex items-center px-3 py-1.5">
                <span class="material-symbols-outlined text-secondary mr-2 text-lg">search</span>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Folio #EVT-..., IP, descripción u operador..." 
                    class="w-full bg-transparent border-0 font-body text-xs text-on-surface focus:outline-none focus:ring-0 placeholder:text-outline p-0"
                />
            </div>

            <div class="sm:col-span-3">
                <select name="entity_type" onchange="this.form.submit()" class="neu-inset w-full px-3 py-1.5 rounded-xl font-body text-xs text-on-surface border-0 focus:ring-1 focus:ring-secondary">
                    <option value="">Todas las Entidades ER</option>
                    <option value="Equipment" {{ request('entity_type') === 'Equipment' ? 'selected' : '' }}>Activos (Equipment)</option>
                    <option value="Loan" {{ request('entity_type') === 'Loan' ? 'selected' : '' }}>Préstamos (Loan)</option>
                    <option value="Incident" {{ request('entity_type') === 'Incident' ? 'selected' : '' }}>Tickets (Incident)</option>
                    <option value="User" {{ request('entity_type') === 'User' ? 'selected' : '' }}>Usuarios (User)</option>
                    <option value="Sanction" {{ request('entity_type') === 'Sanction' ? 'selected' : '' }}>Sanciones (Sanction)</option>
                    <option value="Auth" {{ request('entity_type') === 'Auth' ? 'selected' : '' }}>Seguridad & Auth</option>
                </select>
            </div>

            <div class="sm:col-span-3">
                <select name="timeframe" onchange="this.form.submit()" class="neu-inset w-full px-3 py-1.5 rounded-xl font-body text-xs text-on-surface border-0 focus:ring-1 focus:ring-secondary">
                    <option value="">Cualquier Fecha</option>
                    <option value="24h" {{ request('timeframe') === '24h' ? 'selected' : '' }}>Jornada Actual (24h)</option>
                    <option value="7d" {{ request('timeframe') === '7d' ? 'selected' : '' }}>Últimos 7 días</option>
                    <option value="30d" {{ request('timeframe') === '30d' ? 'selected' : '' }}>Último mes (30d)</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full neu-btn-primary py-1.5 px-3 rounded-xl font-headline text-xs font-semibold">
                    Filtrar
                </button>
                @if (request()->hasAny(['search', 'entity_type', 'risk_level', 'timeframe', 'action']))
                    <a href="{{ route('audit.index') }}" class="neu-btn-secondary p-1.5 rounded-xl text-outline hover:text-error" title="Limpiar filtros">
                        <span class="material-symbols-outlined text-base">filter_alt_off</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla Cronológica de Movimientos -->
    <div class="neu-raised rounded-2xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse font-body text-xs">
                <thead>
                    <tr class="border-b border-outline-variant/40 bg-surface-container-low dark:bg-primary-container/40">
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Folio / Timestamp</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Entidad ER</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Operación</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Operador / IP</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Resumen del Movimiento</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Riesgo</th>
                        <th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase text-right">Forense</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-surface-container/50 dark:hover:bg-primary/40 transition-colors">
                            <!-- Folio / Timestamp -->
                            <td class="p-3.5">
                                <span class="font-mono font-bold text-primary dark:text-surface block">{{ $log->event_folio }}</span>
                                <span class="font-mono text-[10px] text-outline">
                                    {{ $log->created_at->format('d/m/Y H:i:s') }}
                                </span>
                            </td>

                            <!-- Entidad ER -->
                            <td class="p-3.5">
                                <span class="px-2 py-0.5 rounded font-mono text-[10px] bg-surface-container dark:bg-primary-container text-primary dark:text-surface font-semibold">
                                    {{ $log->entity_type }} #{{ $log->entity_id ?? '0' }}
                                </span>
                            </td>

                            <!-- Operación -->
                            <td class="p-3.5">
                                <span class="font-mono text-xs font-bold text-secondary">
                                    {{ $log->action }}
                                </span>
                            </td>

                            <!-- Operador / IP -->
                            <td class="p-3.5">
                                <span class="font-headline font-semibold text-primary dark:text-surface block">
                                    {{ $log->user->name ?? 'Sistema' }}
                                </span>
                                <span class="font-mono text-[10px] text-outline">
                                    IP: {{ $log->ip_address }}
                                </span>
                            </td>

                            <!-- Resumen -->
                            <td class="p-3.5 max-w-md">
                                <p class="text-on-surface line-clamp-2">{{ $log->description }}</p>
                            </td>

                            <!-- Riesgo -->
                            <td class="p-3.5">
                                @if ($log->risk_level === 'critico')
                                    <span class="px-2 py-0.5 rounded-full bg-error-container text-on-error-container font-mono text-[10px] font-bold uppercase animate-pulse">
                                        Crítico
                                    </span>
                                @elseif ($log->risk_level === 'advertencia')
                                    <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-300 font-mono text-[10px] font-bold uppercase">
                                        Advertencia
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-800 font-mono text-[10px]">
                                        Normal
                                    </span>
                                @endif
                            </td>

                            <!-- Forense -->
                            <td class="p-3.5 text-right">
                                <button type="button" onclick="inspectForensicLog({{ $log->id }})" class="p-1 rounded-lg hover:bg-surface-container text-on-surface-variant hover:text-primary transition-colors" title="Inspeccionar hash y payload">
                                    <span class="material-symbols-outlined text-base">saved_search</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-outline">
                                <span class="material-symbols-outlined text-3xl mb-1 block">verified_user</span>
                                <p class="font-headline font-semibold text-sm">No hay eventos en la ventana de auditoría seleccionada.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="p-3.5 border-t border-outline-variant/30 flex justify-between items-center">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>

<!-- ========================================================================= -->
<!-- MODAL: EXPORTACIÓN FORENSE AVANZADA                                       -->
<!-- ========================================================================= -->
<div id="forensicExportModal" class="fixed inset-0 z-50 bg-primary/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="neu-raised rounded-2xl w-full max-w-lg p-6 border border-outline-variant/40 shadow-xl">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30 mb-4">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary text-xl">file_download</span>
                <h3 class="font-headline text-lg font-bold text-primary dark:text-surface">Reporte Forense & Exportación</h3>
            </div>
            <button type="button" onclick="closeExportModal()" class="text-outline hover:text-primary">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form method="GET" action="{{ route('audit.export') }}" class="space-y-4">
            <div class="space-y-1">
                <label class="block font-mono text-xs font-bold text-primary">Ventana Temporal de Auditoría</label>
                <select name="timeframe" class="neu-inset w-full px-3 py-2 rounded-xl text-xs">
                    <option value="24h">Jornada Actual (Últimas 24 horas)</option>
                    <option value="7d">Últimos 7 días de Operación</option>
                    <option value="all">Histórico Completo</option>
                </select>
            </div>

            <div class="space-y-1">
                <label class="block font-mono text-xs font-bold text-primary">Niveles de Riesgo a Incluir</label>
                <div class="grid grid-cols-3 gap-2 font-mono text-xs pt-1">
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" name="risk_levels[]" value="critico" checked class="rounded text-primary-container" />
                        <span class="text-error font-bold">Crítico</span>
                    </label>
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" name="risk_levels[]" value="advertencia" checked class="rounded text-primary-container" />
                        <span class="text-amber-700 font-bold">Advertencia</span>
                    </label>
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="checkbox" name="risk_levels[]" value="normal" checked class="rounded text-primary-container" />
                        <span>Normal</span>
                    </label>
                </div>
            </div>

            <div class="space-y-1">
                <label class="block font-mono text-xs font-bold text-primary">Formato de Salida Certificado</label>
                <div class="space-y-2 pt-1 font-body text-xs">
                    <label class="neu-inset p-2.5 rounded-xl flex items-center justify-between cursor-pointer">
                        <div>
                            <span class="font-mono font-bold text-primary block">CSV Forense (RFC-4180 con UTF-8 BOM)</span>
                            <span class="text-[11px] text-outline">Formato estándar compatible con hojas de cálculo y auditorías</span>
                        </div>
                        <input type="radio" name="export_format" value="csv" checked class="text-primary-container" />
                    </label>
                    <label class="neu-inset p-2.5 rounded-xl flex items-center justify-between cursor-pointer">
                        <div>
                            <span class="font-mono font-bold text-primary block">JSON Estructurado (OpenAPI Spec)</span>
                            <span class="text-[11px] text-outline">Objeto serializado completo con diffs y metadatos técnicos</span>
                        </div>
                        <input type="radio" name="export_format" value="json" class="text-primary-container" />
                    </label>
                </div>
            </div>

            <div class="pt-3 border-t border-outline-variant/30 flex justify-end gap-2">
                <button type="button" onclick="closeExportModal()" class="neu-btn-secondary px-4 py-2 rounded-xl font-mono text-xs font-bold text-outline">
                    Cancelar
                </button>
                <button type="submit" class="neu-btn-primary px-5 py-2 rounded-xl font-headline font-semibold text-xs flex items-center gap-1.5 cursor-pointer">
                    <span class="material-symbols-outlined text-base">download</span>
                    <span>Descargar Reporte</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openExportModal() {
        document.getElementById('forensicExportModal').classList.remove('hidden');
    }
    function closeExportModal() {
        document.getElementById('forensicExportModal').classList.add('hidden');
    }

    function inspectForensicLog(logId) {
        fetch(`/bitacora/${logId}`, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(data => {
                alert(`Inspección Forense:\nFolio: ${data.log.event_folio}\nHash de Integridad (SHA-256): ${data.integrity_hash}\nOperador: ${data.log.user ? data.log.user.name : 'Sistema'}\nIP: ${data.log.ip_address}\nUser Agent: ${data.log.user_agent}\nDescripción: ${data.log.description}`);
            })
            .catch(err => console.error(err));
    }
</script>
@endsection


@extends('layouts.app')

@section('title', 'Acta Digital de Préstamo ' . $loan->folio)

@section('header')
    <div>
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('loans.index') }}" class="text-secondary hover:text-primary flex items-center font-mono text-xs gap-1">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                <span>Volver a Préstamos</span>
            </a>
            <span class="text-outline text-xs">•</span>
            <span class="font-mono text-xs text-outline">Acta Oficial de Resguardo</span>
        </div>
        <h1 class="font-headline text-xl sm:text-2xl font-bold text-primary dark:text-surface tracking-tight flex items-center gap-2">
            <span>Folio: {{ $loan->folio }}</span>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold
                @if ($loan->status === 'entregado') bg-emerald-100 text-emerald-800
                @elseif ($loan->status === 'solicitado') bg-amber-100 text-amber-800
                @elseif ($loan->status === 'devuelto') bg-gray-100 text-gray-800
                @else bg-surface-container text-primary @endif">
                {{ strtoupper(str_replace('_', ' ', $loan->status)) }}
            </span>
        </h1>
    </div>

    <div class="flex items-center gap-2">
        <button onclick="window.print()" class="neu-btn-secondary px-3.5 py-2 rounded-xl font-mono text-xs font-bold text-secondary flex items-center gap-1.5 cursor-pointer">
            <span class="material-symbols-outlined text-base">print</span>
            <span>Imprimir Acta</span>
        </button>
    </div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Tarjeta Principal del Acta -->
    <div class="neu-raised rounded-2xl p-6 sm:p-8 space-y-6">
        
        <!-- Encabezado Institucional del Acta -->
        <div class="border-b border-outline-variant/30 pb-6 flex flex-col sm:flex-row justify-between gap-4 items-start sm:items-center">
            <div>
                <p class="font-mono text-xs text-secondary uppercase font-bold tracking-wider">Universidad Politécnica de Texcoco</p>
                <h2 class="font-headline text-lg font-bold text-primary dark:text-surface mt-0.5">Control de Activos y Préstamos de Precisión</h2>
                <p class="font-body text-xs text-on-surface-variant">Dirección de Tecnologías de la Información y Soporte Técnico</p>
            </div>
            <div class="text-right">
                <span class="font-mono text-xs text-outline block">Fecha de Emisión:</span>
                <span class="font-mono text-xs font-bold text-primary dark:text-surface">{{ $loan->request_date->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        <!-- Información del Custodio y Destino -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="neu-inset p-4 rounded-xl space-y-2">
                <span class="font-mono text-[10px] text-secondary font-bold uppercase tracking-wider block">Datos del Custodio (Solicitante)</span>
                <p class="font-headline font-bold text-sm text-primary dark:text-surface">{{ $loan->user->name ?? 'N/A' }}</p>
                <div class="font-mono text-xs text-on-surface-variant space-y-1">
                    <p><span class="text-outline">Matrícula/ID:</span> {{ $loan->user->institutional_id ?? 'N/A' }}</p>
                    <p><span class="text-outline">Carrera/Depto:</span> {{ $loan->user->career_or_department ?? 'N/D' }}</p>
                    <p><span class="text-outline">Correo:</span> {{ $loan->user->email ?? 'N/D' }}</p>
                    <p><span class="text-outline">Teléfono:</span> {{ $loan->user->phone ?? 'N/D' }}</p>
                </div>
            </div>

            <div class="neu-inset p-4 rounded-xl space-y-2">
                <span class="font-mono text-[10px] text-secondary font-bold uppercase tracking-wider block">Condiciones del Préstamo</span>
                <div class="font-mono text-xs text-on-surface-variant space-y-1.5">
                    <p><span class="text-outline font-bold">Ubicación de Uso:</span> {{ $loan->location_of_use ?? 'Campus Central' }}</p>
                    <p><span class="text-outline font-bold">Propósito:</span> {{ $loan->purpose }}</p>
                    <p><span class="text-outline font-bold">Fecha de Entrega:</span> {{ $loan->loan_date ? $loan->loan_date->format('d/m/Y H:i') : 'Pendiente' }}</p>
                    <p><span class="text-outline font-bold">Fecha Límite:</span> <span class="{{ $loan->isOverdue() ? 'text-error font-bold' : '' }}">{{ $loan->expected_return_date->format('d/m/Y H:i') }}</span></p>
                    <p><span class="text-outline font-bold">Fecha Devolución Real:</span> {{ $loan->actual_return_date ? $loan->actual_return_date->format('d/m/Y H:i') : 'No devuelto aún' }}</p>
                </div>
            </div>
        </div>

        <!-- Tabla de Activos Amparados -->
        <div class="space-y-2">
            <h3 class="font-headline font-bold text-sm text-primary dark:text-surface">
                Equipos y Bienes Asignados en el Acta ({{ $loan->items->count() }})
            </h3>
            
            <div class="neu-inset rounded-xl overflow-hidden">
                <table class="w-full text-left font-body text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-outline-variant/30 bg-surface-container-low">
                            <th class="p-3 font-mono text-[10px] font-bold text-secondary uppercase">Código</th>
                            <th class="p-3 font-mono text-[10px] font-bold text-secondary uppercase">Activo / Serie</th>
                            <th class="p-3 font-mono text-[10px] font-bold text-secondary uppercase">Estado al Entregar</th>
                            <th class="p-3 font-mono text-[10px] font-bold text-secondary uppercase">Estado al Devolver</th>
                            <th class="p-3 font-mono text-[10px] font-bold text-secondary uppercase">Accesorios</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/20">
                        @foreach ($loan->items as $item)
                            <tr>
                                <td class="p-3 font-mono font-bold text-primary">{{ $item->equipment->inventory_code ?? 'ACT-000' }}</td>
                                <td class="p-3">
                                    <span class="font-headline font-semibold text-primary block">{{ $item->equipment->name ?? 'Equipo' }}</span>
                                    <span class="font-mono text-[10px] text-outline">SN: {{ $item->equipment->serial_number ?? 'N/A' }}</span>
                                </td>
                                <td class="p-3 font-mono text-[11px] capitalize">{{ $item->condition_on_loan }}</td>
                                <td class="p-3 font-mono text-[11px] capitalize">
                                    {{ $item->condition_on_return ?? 'En resguardo' }}
                                    @if ($item->notes_on_return)
                                        <span class="block text-[10px] text-outline">Obs: {{ $item->notes_on_return }}</span>
                                    @endif
                                </td>
                                <td class="p-3 text-[11px] text-outline">{{ $item->accessories_included ?? 'Estándar' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Firmas y Trazabilidad Operativa -->
        <div class="pt-6 border-t border-outline-variant/30 grid grid-cols-1 sm:grid-cols-3 gap-6 text-center font-mono text-xs">
            <div class="p-3 border-t border-dashed border-outline-variant/60">
                <p class="font-bold text-primary">{{ $loan->user->name ?? 'Custodio' }}</p>
                <p class="text-[10px] text-outline mt-1">Firma del Custodio Receptor</p>
            </div>
            <div class="p-3 border-t border-dashed border-outline-variant/60">
                <p class="font-bold text-primary">{{ $loan->authorizedBy->name ?? 'Autorizador' }}</p>
                <p class="text-[10px] text-outline mt-1">Autorización / Supervisión</p>
            </div>
            <div class="p-3 border-t border-dashed border-outline-variant/60">
                <p class="font-bold text-primary">{{ $loan->dispatchedBy->name ?? ($loan->receivedBy->name ?? 'Ventanilla') }}</p>
                <p class="text-[10px] text-outline mt-1">Operador de Ventanilla TI</p>
            </div>
        </div>

    </div>

</div>
@endsection


@extends('layouts.app')

@section('title', 'Mi bitácora')

@section('header')
    <div>
        <p class="font-mono text-[11px] text-secondary font-bold uppercase">Historial personal</p>
        <h1 class="font-headline text-xl sm:text-2xl font-bold text-primary dark:text-surface">Mi bitácora</h1>
        <p class="font-body text-xs text-on-surface-variant dark:text-inverse-primary mt-1">Solo tú puedes consultar los eventos asociados a tu cuenta.</p>
    </div>
    <a href="{{ route('portal.reports.create') }}" class="neu-btn-primary px-3.5 py-2 rounded-xl font-mono text-xs font-bold inline-flex items-center gap-1.5"><span class="material-symbols-outlined text-base">report</span><span>Enviar reporte</span></a>
@endsection

@section('content')
<section class="neu-raised rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left font-body text-sm">
            <thead class="bg-surface-container-low dark:bg-primary-container/40"><tr><th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Fecha</th><th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Evento</th><th class="p-3.5 font-mono text-[11px] font-bold text-secondary uppercase">Detalle</th></tr></thead>
            <tbody class="divide-y divide-outline-variant/30">
                @forelse ($activityLogs as $entry)
                    <tr>
                        <td class="p-3.5 whitespace-nowrap font-mono text-xs text-outline">{{ $entry->created_at->format('d/m/Y H:i') }}</td>
                        <td class="p-3.5 font-semibold text-primary dark:text-surface">{{ $entry->displayName() }}</td>
                        <td class="p-3.5 text-on-surface-variant">@if ($entry->subject){{ $entry->subject->name ?? $entry->subject->title ?? $entry->subject->folio ?? $entry->subject->ticket_code ?? class_basename($entry->subject_type) . ' #' . $entry->subject_id }}@else Actividad de cuenta @endif</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="p-8 text-center text-outline">Aún no hay eventos en tu bitácora.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($activityLogs->hasPages())<div class="p-4 border-t border-outline-variant/30">{{ $activityLogs->links() }}</div>@endif
</section>
@endsection
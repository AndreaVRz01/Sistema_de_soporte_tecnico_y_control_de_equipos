@extends('layouts.app')

@section('title', 'Enviar reporte')

@section('header')
    <div><h1 class="font-headline text-xl sm:text-2xl font-bold text-primary dark:text-surface">Reportar un problema</h1><p class="font-body text-xs text-on-surface-variant dark:text-inverse-primary mt-1">Describe la falla para que el equipo de soporte pueda atenderla.</p></div>
    <a href="{{ route('dashboard') }}" class="neu-btn-secondary px-3.5 py-2 rounded-xl font-mono text-xs font-bold text-secondary inline-flex items-center gap-1.5"><span class="material-symbols-outlined text-base">arrow_back</span><span>Volver a mi panel</span></a>
@endsection

@section('content')
<div class="max-w-3xl">
    <form method="POST" action="{{ route('portal.reports.store') }}" class="neu-raised rounded-2xl p-5 sm:p-7 space-y-5">
        @csrf
        <div class="space-y-1">
            <label for="equipment_id" class="block font-mono text-xs font-bold text-primary dark:text-surface">Activo relacionado *</label>
            <select id="equipment_id" name="equipment_id" required class="neu-inset w-full px-3 py-2.5 rounded-xl text-sm">
                <option value="">Selecciona un equipo</option>
                @foreach ($equipment as $item)<option value="{{ $item->id }}" @selected((string) old('equipment_id') === (string) $item->id)>{{ $item->inventory_code }} · {{ $item->name }} ({{ $item->location }})</option>@endforeach
            </select>
        </div>
        <div class="space-y-1"><label for="title" class="block font-mono text-xs font-bold text-primary dark:text-surface">Asunto *</label><input id="title" name="title" value="{{ old('title') }}" maxlength="150" required class="neu-inset w-full px-3 py-2.5 rounded-xl text-sm" placeholder="Ej. El proyector no enciende" /></div>
        <div class="space-y-1">
            <label for="type" class="block font-mono text-xs font-bold text-primary dark:text-surface">Tipo de problema *</label>
            <select id="type" name="type" required class="neu-inset w-full px-3 py-2.5 rounded-xl text-sm"><option value="hardware">Falla de hardware</option><option value="software">Falla de software</option><option value="daño_fisico">Daño físico</option><option value="desgaste_natural">Desgaste natural</option><option value="perdida_accesorio">Accesorio perdido</option><option value="mantenimiento_preventivo">Mantenimiento preventivo</option></select>
        </div>
        <div class="space-y-1"><label for="description" class="block font-mono text-xs font-bold text-primary dark:text-surface">Descripción *</label><textarea id="description" name="description" rows="5" required class="neu-inset w-full px-3 py-2.5 rounded-xl text-sm" placeholder="Indica qué ocurrió, cuándo y cualquier detalle útil.">{{ old('description') }}</textarea></div>
        <div class="flex justify-end border-t border-outline-variant/30 pt-4"><button type="submit" class="neu-btn-primary px-5 py-2.5 rounded-xl font-headline font-semibold text-sm inline-flex items-center gap-2"><span class="material-symbols-outlined text-base">send</span><span>Enviar reporte</span></button></div>
    </form>
</div>
@endsection
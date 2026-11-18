@extends('layouts.app')

@section('title', 'Solicitudes de registro')

@section('header')
    <div>
        <h1 class="font-headline text-xl font-bold text-primary dark:text-surface sm:text-2xl">Solicitudes de registro</h1>
        <p class="mt-1 font-body text-xs text-on-surface-variant dark:text-inverse-primary">
            Revisa y aprueba las cuentas de alumnos y profesores antes de permitirles iniciar sesión.
        </p>
    </div>
@endsection

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between gap-3 rounded-2xl bg-surface-container-low p-4 dark:bg-primary-container">
        <div>
            <p class="font-mono text-[10px] font-bold uppercase tracking-widest text-secondary">Pendientes de revisión</p>
            <p class="mt-1 font-headline text-lg font-bold text-primary dark:text-surface">{{ $pendingRegistrations->total() }} {{ $pendingRegistrations->total() === 1 ? 'solicitud' : 'solicitudes' }}</p>
        </div>
        <span class="material-symbols-outlined text-3xl text-secondary">person_check</span>
    </div>

    @forelse ($pendingRegistrations as $registration)
        <article class="rounded-2xl border border-outline-variant/50 bg-white p-5 shadow-sm dark:bg-primary-container sm:p-6">
            <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-center">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="font-headline text-lg font-bold text-primary dark:text-surface">{{ $registration->name }}</h2>
                        <span class="rounded-full bg-amber-100 px-2.5 py-1 font-mono text-[10px] font-bold uppercase text-amber-900">
                            {{ $registration->user_type === \App\Models\User::TYPE_ALUMNO ? 'Alumno' : 'Profesor' }}
                        </span>
                    </div>
                    <p class="mt-1 break-all font-body text-sm text-on-surface-variant dark:text-inverse-primary">{{ $registration->email }}</p>
                    <dl class="mt-3 grid grid-cols-1 gap-x-8 gap-y-2 text-xs sm:grid-cols-2 xl:grid-cols-3">
                        <div>
                            <dt class="font-mono text-[10px] uppercase text-outline">Matrícula / clave</dt>
                            <dd class="mt-0.5 font-semibold text-primary dark:text-surface">{{ $registration->institutional_id ?: 'No proporcionada' }}</dd>
                        </div>
                        <div>
                            <dt class="font-mono text-[10px] uppercase text-outline">Carrera / departamento</dt>
                            <dd class="mt-0.5 text-on-surface-variant dark:text-inverse-primary">{{ $registration->career_or_department ?: 'No especificado' }}</dd>
                        </div>
                        <div>
                            <dt class="font-mono text-[10px] uppercase text-outline">Teléfono</dt>
                            <dd class="mt-0.5 text-on-surface-variant dark:text-inverse-primary">{{ $registration->phone ?: 'No especificado' }}</dd>
                        </div>
                        <div>
                            <dt class="font-mono text-[10px] uppercase text-outline">Solicitud recibida</dt>
                            <dd class="mt-0.5 text-on-surface-variant dark:text-inverse-primary">{{ $registration->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="flex shrink-0 flex-col gap-2 sm:flex-row">
                    <form method="POST" action="{{ route('users.registrations.approve', $registration) }}" onsubmit="return confirm('¿Aprobar esta cuenta? La persona podrá iniciar sesión después de la aprobación.')">
                        @csrf
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl bg-emerald-700 px-4 py-2.5 font-mono text-xs font-bold uppercase tracking-wide text-white transition hover:bg-emerald-800 sm:w-auto">
                            <span class="material-symbols-outlined text-base">check</span>Aprobar
                        </button>
                    </form>
                    <form method="POST" action="{{ route('users.registrations.reject', $registration) }}" onsubmit="return confirm('¿Rechazar esta solicitud? La cuenta no podrá iniciar sesión.')">
                        @csrf
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl border border-error/30 bg-error-container px-4 py-2.5 font-mono text-xs font-bold uppercase tracking-wide text-on-error-container transition hover:brightness-95 sm:w-auto">
                            <span class="material-symbols-outlined text-base">close</span>Rechazar
                        </button>
                    </form>
                </div>
            </div>
        </article>
    @empty
        <div class="rounded-2xl border border-outline-variant/50 bg-white px-6 py-14 text-center shadow-sm dark:bg-primary-container">
            <span class="material-symbols-outlined text-5xl text-outline">task_alt</span>
            <h2 class="mt-3 font-headline text-lg font-bold text-primary dark:text-surface">No hay registros pendientes</h2>
            <p class="mt-1 text-sm text-on-surface-variant dark:text-inverse-primary">Las nuevas solicitudes aparecerán aquí para su revisión.</p>
        </div>
    @endforelse

    @if ($pendingRegistrations->hasPages())
        <div>{{ $pendingRegistrations->links() }}</div>
    @endif
</div>
@endsection

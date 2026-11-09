@extends('layouts.public')

@section('title', 'Restablecer contraseña')

@section('content')
<section class="w-full max-w-xl mx-auto py-10 sm:py-14">
    <div class="neu-raised rounded-2xl p-6 sm:p-8">
        <div class="mb-6 border-b border-outline-variant/30 pb-4">
            <p class="font-mono text-[11px] font-bold uppercase text-secondary dark:text-secondary-fixed">Seguridad de cuenta</p>
            <h1 class="mt-1 font-headline text-2xl font-bold text-primary dark:text-surface">Crea una nueva contraseña</h1>
            <p class="mt-2 font-body text-sm text-on-surface-variant dark:text-inverse-primary">Elige una contraseña de al menos 8 caracteres que no hayas usado antes.</p>
        </div>

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}" />
            <input type="hidden" name="email" value="{{ old('email', $email) }}" />

            <div class="space-y-1">
                <label for="password" class="block font-mono text-xs font-bold text-primary dark:text-surface">Nueva contraseña</label>
                <input id="password" type="password" name="password" required minlength="8" autocomplete="new-password" class="neu-inset w-full rounded-xl px-3.5 py-3 text-sm text-primary dark:text-surface placeholder:text-outline focus:outline-none focus:ring-2 focus:ring-secondary" />
            </div>

            <div class="space-y-1">
                <label for="password_confirmation" class="block font-mono text-xs font-bold text-primary dark:text-surface">Confirmar contraseña</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" class="neu-inset w-full rounded-xl px-3.5 py-3 text-sm text-primary dark:text-surface placeholder:text-outline focus:outline-none focus:ring-2 focus:ring-secondary" />
            </div>

            <button type="submit" class="neu-btn-primary w-full rounded-xl py-3 font-headline text-sm font-semibold">
                Guardar contraseña
            </button>
        </form>

        <a href="{{ route('login') }}" class="mt-5 inline-flex items-center gap-1.5 font-mono text-xs font-semibold text-secondary dark:text-secondary-fixed hover:underline">
            <span class="material-symbols-outlined text-base">arrow_back</span>
            <span>Volver a iniciar sesión</span>
        </a>
    </div>
</section>
@endsection
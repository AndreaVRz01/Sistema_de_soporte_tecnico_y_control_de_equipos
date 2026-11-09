@extends('layouts.app')

@section('title', 'Registrar Solicitante')

@section('header')
    <div>
        <h1 class="font-headline text-xl sm:text-2xl font-bold text-primary dark:text-surface tracking-tight">
            Registrar solicitante
        </h1>
        <p class="font-body text-xs text-on-surface-variant dark:text-inverse-primary mt-1">
            Alta de una persona de la comunidad universitaria para continuar con su solicitud de préstamo.
        </p>
    </div>
    <a href="{{ route('loans.index') }}" class="neu-btn-secondary px-3.5 py-2 rounded-xl font-mono text-xs font-bold text-secondary flex items-center gap-1.5">
        <span class="material-symbols-outlined text-base">arrow_back</span>
        <span>Volver a préstamos</span>
    </a>
@endsection

@section('content')
    <div class="max-w-3xl">
        <form method="POST" action="{{ route('loans.borrowers.store') }}" class="neu-raised rounded-2xl p-5 sm:p-7 space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1 sm:col-span-2">
                    <label class="block font-mono text-xs font-bold text-primary dark:text-surface" for="borrower-name">Nombre completo *</label>
                    <input id="borrower-name" name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name" class="neu-inset w-full px-3 py-2.5 rounded-xl text-sm" />
                </div>

                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary dark:text-surface" for="borrower-id">Matrícula o número de empleado *</label>
                    <input id="borrower-id" name="institutional_id" value="{{ old('institutional_id') }}" required maxlength="50" class="neu-inset w-full px-3 py-2.5 rounded-xl text-sm font-mono" />
                </div>
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary dark:text-surface" for="borrower-type">Tipo institucional *</label>
                    <select id="borrower-type" name="user_type" required class="neu-inset w-full px-3 py-2.5 rounded-xl text-sm">
                        <option value="alumno" @selected(old('user_type') === 'alumno')>Alumno</option>
                        <option value="docente" @selected(old('user_type') === 'docente')>Docente</option>
                        <option value="administrativo" @selected(old('user_type') === 'administrativo')>Administrativo</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary dark:text-surface" for="borrower-email">Correo institucional *</label>
                    <input id="borrower-email" type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" class="neu-inset w-full px-3 py-2.5 rounded-xl text-sm" />
                </div>
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary dark:text-surface" for="borrower-phone">Teléfono</label>
                    <input id="borrower-phone" name="phone" value="{{ old('phone') }}" maxlength="25" autocomplete="tel" class="neu-inset w-full px-3 py-2.5 rounded-xl text-sm" />
                </div>

                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary dark:text-surface" for="borrower-department">Carrera o departamento</label>
                    <input id="borrower-department" name="career_or_department" value="{{ old('career_or_department') }}" maxlength="255" class="neu-inset w-full px-3 py-2.5 rounded-xl text-sm" />
                </div>
                <div class="space-y-1">
                    <label class="block font-mono text-xs font-bold text-primary dark:text-surface" for="borrower-password">Contraseña inicial *</label>
                    <input id="borrower-password" type="password" name="password" required minlength="8" autocomplete="new-password" class="neu-inset w-full px-3 py-2.5 rounded-xl text-sm" />
                </div>
                <div class="space-y-1 sm:col-start-2">
                    <label class="block font-mono text-xs font-bold text-primary dark:text-surface" for="borrower-password-confirmation">Confirmar contraseña *</label>
                    <input id="borrower-password-confirmation" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" class="neu-inset w-full px-3 py-2.5 rounded-xl text-sm" />
                </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 border-t border-outline-variant/30 pt-4">
                <a href="{{ route('loans.index') }}" class="neu-btn-secondary px-4 py-2 rounded-xl font-mono text-xs font-bold text-outline text-center">Cancelar</a>
                <button type="submit" class="neu-btn-primary px-5 py-2 rounded-xl font-headline font-semibold text-xs flex items-center justify-center gap-1.5">
                    <span class="material-symbols-outlined text-base">person_add</span>
                    <span>Registrar y continuar</span>
                </button>
            </div>
        </form>
    </div>
@endsection
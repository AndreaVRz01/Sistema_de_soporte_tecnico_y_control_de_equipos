@extends('layouts.public')

@section('title', 'Crear cuenta institucional')

@section('content')
<section class="mx-auto max-w-3xl px-4 py-10 sm:px-6">
    <div class="overflow-hidden rounded-3xl border border-outline-variant/50 bg-white shadow-sm dark:bg-primary-container">
        <div class="bg-primary-container px-6 py-7 text-white sm:px-9">
            <span class="font-mono text-[10px] font-bold uppercase tracking-widest text-secondary-container">Comunidad universitaria</span>
            <h1 class="mt-2 font-headline text-2xl font-bold sm:text-3xl">Crea tu cuenta SIGA</h1>
            <p class="mt-2 max-w-xl text-sm leading-relaxed text-white/75">
                Completa tus datos. Un administrador, supervisor o encargado de préstamos revisará la solicitud antes de habilitar el acceso.
            </p>
        </div>

        <form method="POST" action="{{ route('register.store') }}" class="space-y-5 p-6 sm:p-9">
            @csrf

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="space-y-1 sm:col-span-2">
                    <label for="register-name" class="font-mono text-xs font-bold text-primary dark:text-surface">Nombre completo *</label>
                    <input id="register-name" name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name" class="w-full rounded-xl border-outline-variant bg-surface px-3 py-2.5 text-sm focus:border-secondary focus:ring-secondary dark:bg-primary" />
                    @error('name')<p class="text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-1">
                    <label for="register-type" class="font-mono text-xs font-bold text-primary dark:text-surface">Tipo de usuario *</label>
                    <select id="register-type" name="user_type" required class="w-full rounded-xl border-outline-variant bg-surface px-3 py-2.5 text-sm focus:border-secondary focus:ring-secondary dark:bg-primary">
                        <option value="alumno" @selected(old('user_type', 'alumno') === 'alumno')>Alumno(a)</option>
                        <option value="docente" @selected(old('user_type') === 'docente')>Profesor(a)</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label id="register-id-label" for="register-id" class="font-mono text-xs font-bold text-primary dark:text-surface">Matrícula *</label>
                    <input id="register-id" name="institutional_id" value="{{ old('institutional_id') }}" maxlength="50" autocomplete="off" class="w-full rounded-xl border-outline-variant bg-surface px-3 py-2.5 text-sm font-mono focus:border-secondary focus:ring-secondary dark:bg-primary" />
                    <p id="register-id-help" class="text-[11px] text-outline">Requerida para validar el registro de estudiante.</p>
                    @error('institutional_id')<p class="text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-1">
                    <label for="register-email" class="font-mono text-xs font-bold text-primary dark:text-surface">Correo electrónico *</label>
                    <input id="register-email" type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" class="w-full rounded-xl border-outline-variant bg-surface px-3 py-2.5 text-sm focus:border-secondary focus:ring-secondary dark:bg-primary" />
                    @error('email')<p class="text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-1">
                    <label for="register-career" class="font-mono text-xs font-bold text-primary dark:text-surface">Carrera / departamento</label>
                    <input id="register-career" name="career_or_department" value="{{ old('career_or_department') }}" maxlength="255" class="w-full rounded-xl border-outline-variant bg-surface px-3 py-2.5 text-sm focus:border-secondary focus:ring-secondary dark:bg-primary" />
                    @error('career_or_department')<p class="text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-1">
                    <label for="register-phone" class="font-mono text-xs font-bold text-primary dark:text-surface">Teléfono</label>
                    <input id="register-phone" name="phone" value="{{ old('phone') }}" maxlength="25" autocomplete="tel" class="w-full rounded-xl border-outline-variant bg-surface px-3 py-2.5 text-sm focus:border-secondary focus:ring-secondary dark:bg-primary" />
                    @error('phone')<p class="text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-1">
                    <label for="register-password" class="font-mono text-xs font-bold text-primary dark:text-surface">Contraseña (mínimo 8 caracteres) *</label>
                    <input id="register-password" type="password" name="password" required minlength="8" autocomplete="new-password" class="w-full rounded-xl border-outline-variant bg-surface px-3 py-2.5 text-sm focus:border-secondary focus:ring-secondary dark:bg-primary" />
                    @error('password')<p class="text-xs text-error">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-1">
                    <label for="register-password-confirmation" class="font-mono text-xs font-bold text-primary dark:text-surface">Confirmar contraseña *</label>
                    <input id="register-password-confirmation" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" class="w-full rounded-xl border-outline-variant bg-surface px-3 py-2.5 text-sm focus:border-secondary focus:ring-secondary dark:bg-primary" />
                </div>
            </div>

            <div class="rounded-xl bg-surface-container-low p-3 text-xs leading-relaxed text-on-surface-variant dark:bg-primary dark:text-inverse-primary">
                El registro no inicia una sesión automáticamente. Podrás ingresar una vez que tu solicitud sea aprobada.
            </div>

            <div class="flex flex-col-reverse justify-between gap-3 border-t border-outline-variant/40 pt-5 sm:flex-row sm:items-center">
                <a href="{{ route('login') }}" class="text-center text-sm font-bold text-primary hover:underline dark:text-secondary-container">Ya tengo cuenta</a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary-container px-6 py-3 font-mono text-xs font-bold uppercase tracking-wider text-white transition hover:bg-primary">
                    <span class="material-symbols-outlined text-base">person_add</span>
                    Enviar para aprobación
                </button>
            </div>
        </form>
    </div>
</section>

<script>
    const registrationType = document.getElementById('register-type');
    const registrationId = document.getElementById('register-id');
    const registrationIdLabel = document.getElementById('register-id-label');
    const registrationIdHelp = document.getElementById('register-id-help');

    function updateRegistrationIdRequirement() {
        const isStudent = registrationType.value === 'alumno';
        registrationId.required = isStudent;
        registrationIdLabel.textContent = isStudent ? 'Matrícula *' : 'Matrícula / clave institucional (opcional)';
        registrationIdHelp.textContent = isStudent
            ? 'Requerida para validar el registro de estudiante.'
            : 'No es necesaria para profesores; puedes dejarla vacía.';
    }

    registrationType.addEventListener('change', updateRegistrationIdRequirement);
    updateRegistrationIdRequirement();
</script>
@endsection

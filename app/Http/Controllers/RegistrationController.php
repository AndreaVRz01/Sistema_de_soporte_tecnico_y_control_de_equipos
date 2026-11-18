<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('public.catalog');
        }

        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'user_type' => ['required', Rule::in([User::TYPE_ALUMNO, User::TYPE_DOCENTE])],
            'institutional_id' => [
                'nullable',
                'required_if:user_type,' . User::TYPE_ALUMNO,
                'string',
                'max:50',
                'unique:users,institutional_id',
            ],
            'career_or_department' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:25'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'institutional_id.required_if' => 'La matrícula es obligatoria para estudiantes.',
            'institutional_id.unique' => 'La matrícula ya está registrada.',
            'email.unique' => 'El correo electrónico ya está registrado.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => User::ROLE_USUARIO,
            'user_type' => $validated['user_type'],
            'institutional_id' => $validated['institutional_id'] ?? null,
            'career_or_department' => $validated['career_or_department'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'status' => 'inactivo',
            'registration_status' => User::REGISTRATION_PENDING,
        ]);

        app(\App\Services\ActivityLogService::class)->record(
            $user,
            'usuario.registro_solicitado',
            $user,
            ['user_type' => $user->user_type]
        );
        AuditService::log(
            'User',
            $user->id,
            'REGISTRO_SOLICITADO',
            "Solicitud de registro pendiente: {$user->name} ({$user->user_type})",
            ['user_type' => $user->user_type],
            'normal'
        );

        return redirect()->route('login')->with(
            'success',
            'Tu solicitud fue enviada. Podrás iniciar sesión cuando sea aprobada por el personal autorizado.'
        );
    }
}

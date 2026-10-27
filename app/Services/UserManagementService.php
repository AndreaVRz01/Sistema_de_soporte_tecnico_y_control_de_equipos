<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserManagementService
{
    public function __construct(private readonly ActivityLogService $activityLog)
    {
    }

    public function registerStaff(User $actor, array $data): User
    {
        $this->authorize($actor, [User::ROLE_ADMIN]);

        $validated = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in([
                User::ROLE_SUPERVISOR,
                User::ROLE_TECNICO,
                User::ROLE_ENCARGADO_ACTIVOS,
                User::ROLE_ENCARGADO_PRESTAMOS,
            ])],
            'user_type' => ['required', Rule::in([
                User::TYPE_ALUMNO,
                User::TYPE_DOCENTE,
                User::TYPE_ADMINISTRATIVO,
            ])],
            'institutional_id' => ['required', 'string', 'max:255', 'unique:users,institutional_id'],
            'career_or_department' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:25'],
        ])->validate();

        return DB::transaction(function () use ($actor, $validated): User {
            $user = User::create($validated);
            $this->activityLog->record($actor, 'usuario.personal_creado', $user, ['role' => $user->role]);

            return $user;
        });
    }

    public function registerRequester(User $actor, array $data): User
    {
        $this->authorize($actor, [User::ROLE_ENCARGADO_PRESTAMOS]);

        $validated = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'user_type' => ['required', Rule::in([User::TYPE_ALUMNO, User::TYPE_DOCENTE])],
            'institutional_id' => ['required', 'string', 'max:255', 'unique:users,institutional_id'],
            'career_or_department' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:25'],
        ])->validate();

        return DB::transaction(function () use ($actor, $validated): User {
            $user = User::create($validated + [
                'role' => User::ROLE_USUARIO,
                'status' => 'activo',
            ]);
            $this->activityLog->record($actor, 'usuario.solicitante_creado', $user);

            return $user;
        });
    }

    private function authorize(User $actor, array $roles): void
    {
        if (!\in_array($actor->role, $roles, true)) {
            throw new AuthorizationException('No tienes permiso para realizar esta operación.');
        }
    }
}
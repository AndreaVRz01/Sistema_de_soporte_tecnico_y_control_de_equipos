<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Muestra la consola de administración de usuarios, roles y matriz de privilegios.
     * Basado en: docs/stitch_asset_loan_maintenance_system/administraci_n_de_usuarios_roles_y_permisos
     */
    public function index(Request $request): View
    {
        $query = User::withCount(['loans', 'reportedIncidents', 'sanctions']);

        // 1. Filtro por Rol Operativo
        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        // 2. Filtro por Tipo Institucional
        if ($userType = $request->input('user_type')) {
            $query->where('user_type', $userType);
        }

        // 3. Filtro por Estado Operativo
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // 4. Búsqueda por Nombre, Correo, No. Empleado/Matrícula o Carrera/Depto
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('institutional_id', 'like', "%{$search}%")
                  ->orWhere('career_or_department', 'like', "%{$search}%");
            });
        }

        // Contadores de cabecera
        $counts = [
            'total' => User::count(),
            'activos' => User::where('status', 'activo')->count(),
            'tecnicos' => User::where('role', User::ROLE_TECNICO)->count(),
            'sancionados' => User::where('status', 'sancionado')->count(),
        ];

        $users = $query->latest()->paginate(10)->withQueryString();

        // Matriz de permisos de la memoria de diseño institucional
        $permissionsMatrix = [
            'admin' => ['dashboard' => true, 'activos' => true, 'prestamos' => true, 'tickets' => true, 'baja' => true, 'bitacora' => true],
            'supervisor' => ['dashboard' => true, 'activos' => true, 'prestamos' => true, 'tickets' => true, 'baja' => false, 'bitacora' => true],
            'encargado_activos' => ['dashboard' => true, 'activos' => true, 'prestamos' => false, 'tickets' => true, 'baja' => true, 'bitacora' => false],
            'encargado_prestamos' => ['dashboard' => true, 'activos' => false, 'prestamos' => true, 'tickets' => false, 'baja' => false, 'bitacora' => false],
            'tecnico' => ['dashboard' => false, 'activos' => false, 'prestamos' => false, 'tickets' => true, 'baja' => false, 'bitacora' => false],
            'usuario' => ['dashboard' => false, 'activos' => false, 'prestamos' => false, 'tickets' => false, 'baja' => false, 'bitacora' => false],
        ];

        return view('private.users.index', compact('users', 'counts', 'permissionsMatrix'));
    }

    /**
     * Registra un nuevo usuario institucional con rol y privilegios.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'institutional_id' => ['required', 'string', 'max:50', 'unique:users,institutional_id'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:admin,supervisor,encargado_activos,encargado_prestamos,tecnico,usuario'],
            'user_type' => ['required', 'in:alumno,docente,administrativo'],
            'career_or_department' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:25'],
            'status' => ['required', 'in:activo,inactivo,sancionado'],
        ], [
            'institutional_id.unique' => 'El número de empleado o matrícula ya se encuentra registrado.',
            'email.unique' => 'El correo electrónico institucional ya está en uso.',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        AuditService::log(
            'User',
            $user->id,
            'USUARIO_CREADO',
            "Alta de operador/usuario: {$user->name} ({$user->role}) - ID: {$user->institutional_id}",
            ['nombre' => $user->name, 'rol' => $user->role, 'email' => $user->email],
            'normal'
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado con éxito.',
                'user' => $user,
            ]);
        }

        return redirect()->route('users.index')->with('success', "Usuario {$user->name} registrado exitosamente.");
    }

    /**
     * Muestra la ficha de usuario y su historial de préstamos, sanciones y tickets.
     */
    public function show(User $user): View|JsonResponse
    {
        $user->load([
            'loans.items.equipment',
            'sanctions',
            'reportedIncidents.equipment',
        ]);

        if (request()->wantsJson()) {
            return response()->json($user);
        }

        return view('private.users.show', compact('user'));
    }

    /**
     * Actualiza el perfil, rol, departamento o datos de un usuario.
     */
    public function update(Request $request, User $user): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'institutional_id' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'in:admin,supervisor,encargado_activos,encargado_prestamos,tecnico,usuario'],
            'user_type' => ['required', 'in:alumno,docente,administrativo'],
            'career_or_department' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:25'],
            'status' => ['required', 'in:activo,inactivo,sancionado'],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $old = $user->only(['name', 'role', 'status', 'career_or_department']);
        $user->update($validated);

        AuditService::log(
            'User',
            $user->id,
            'USUARIO_ACTUALIZADO',
            "Modificación de cuenta institucional para: {$user->name}",
            ['antes' => $old, 'despues' => $user->only(['name', 'role', 'status', 'career_or_department'])],
            'normal'
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado correctamente.',
                'user' => $user,
            ]);
        }

        return redirect()->route('users.index')->with('success', "Datos de {$user->name} actualizados.");
    }

    /**
     * Alterna rápidamente el estado operativo del usuario (Activo / Inactivo).
     */
    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        $newStatus = ($user->status === 'activo') ? 'inactivo' : 'activo';
        $user->update(['status' => $newStatus]);

        AuditService::log(
            'User',
            $user->id,
            'USUARIO_STATUS_CAMBIO',
            "Cambio de estatus para {$user->name}: {$newStatus}",
            ['nuevo_estatus' => $newStatus],
            ($newStatus === 'inactivo') ? 'advertencia' : 'normal'
        );

        return back()->with('success', "El estatus de {$user->name} fue cambiado a: " . strtoupper($newStatus));
    }

    /**
     * Restablece la contraseña institucional del usuario y emite clave temporal.
     */
    public function resetCredentials(Request $request, User $user): RedirectResponse
    {
        $tempPass = 'Siga' . rand(1000, 9999) . '!';
        $user->update(['password' => Hash::make($tempPass)]);

        AuditService::log(
            'User',
            $user->id,
            'PASSWORD_RESET_ADMIN',
            "Contraseña temporal generada para {$user->email} por administrador",
            null,
            'advertencia'
        );

        return back()->with('success', "Credencial provisional emitida para {$user->name}. Clave temporal: {$tempPass}");
    }
}


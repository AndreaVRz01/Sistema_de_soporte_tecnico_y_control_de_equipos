<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

it('permite únicamente los roles indicados en la ruta', function (string $role, int $status): void {
    Route::get('/_test/role-middleware', fn () => response()->noContent())
        ->middleware('role:admin,supervisor');

    $user = User::factory()->make(['role' => $role]);

    $this->actingAs($user)
        ->get('/_test/role-middleware')
        ->assertStatus($status);
})->with([
    'Administrador' => [User::ROLE_ADMIN, 204],
    'Supervisor' => [User::ROLE_SUPERVISOR, 204],
    'Tecnico' => [User::ROLE_TECNICO, 403],
    'Encargado de activos' => [User::ROLE_ENCARGADO_ACTIVOS, 403],
    'Encargado de prestamos' => [User::ROLE_ENCARGADO_PRESTAMOS, 403],
    'Usuario solicitante' => [User::ROLE_USUARIO, 403],
]);

it('deniega el acceso cuando no hay usuario autenticado', function (): void {
    Route::get('/_test/role-middleware', fn () => response()->noContent())
        ->middleware('role:admin');

    $this->get('/_test/role-middleware')->assertForbidden();
});

it('permite registrar solicitantes únicamente al encargado de préstamos', function (string $role, int $status): void {
    $this->actingAs(User::factory()->make(['role' => $role]))
        ->get(route('loans.borrowers.create'))
        ->assertStatus($status);
})->with([
    'Encargado de préstamos' => [User::ROLE_ENCARGADO_PRESTAMOS, 200],
    'Administrador' => [User::ROLE_ADMIN, 403],
    'Técnico' => [User::ROLE_TECNICO, 403],
]);

it('oculta la recuperación de contraseña al abrir el inicio de sesión', function (): void {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('¿Olvidaste tu contraseña?')
        ->assertSee('id="view-recovery"', false)
        ->assertSee('class="space-y-6 hidden"', false)
        ->assertDontSee('Recuperar con Token');
});

it('deniega a los técnicos las rutas administrativas', function (): void {
    $this->actingAs(User::factory()->make(['role' => User::ROLE_TECNICO]))
        ->get('/usuarios')
        ->assertForbidden();
});

it('mantiene cada perfil fuera de módulos asignados a otros roles', function (string $role, string $path): void {
    $this->actingAs(User::factory()->make(['role' => $role]))
        ->get($path)
        ->assertForbidden();
})->with([
    'Activos no gestiona préstamos' => [User::ROLE_ENCARGADO_ACTIVOS, '/prestamos'],
    'Préstamos no gestiona activos' => [User::ROLE_ENCARGADO_PRESTAMOS, '/activos'],
    'Técnico no gestiona sanciones' => [User::ROLE_TECNICO, '/sanciones'],
    'Usuario no entra al dashboard operativo' => [User::ROLE_USUARIO, '/dashboard'],
]);

it('redirige cada rol autenticado a su módulo de inicio', function (string $role, string $route): void {
    $this->actingAs(User::factory()->make(['role' => $role]))
        ->get(route('login'))
        ->assertRedirect(route($route));
})->with([
    'Administrador' => [User::ROLE_ADMIN, 'dashboard'],
    'Encargado de activos' => [User::ROLE_ENCARGADO_ACTIVOS, 'equipment.index'],
    'Encargado de préstamos' => [User::ROLE_ENCARGADO_PRESTAMOS, 'loans.index'],
    'Técnico' => [User::ROLE_TECNICO, 'incidents.index'],
    'Usuario solicitante' => [User::ROLE_USUARIO, 'public.catalog'],
]);
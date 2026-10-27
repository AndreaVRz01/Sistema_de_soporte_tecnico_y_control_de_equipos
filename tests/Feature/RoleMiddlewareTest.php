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
<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function registrationPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Persona de prueba',
        'email' => 'persona@example.test',
        'user_type' => User::TYPE_ALUMNO,
        'institutional_id' => 'ALU-TEST-100',
        'password' => 'ClaveSegura123!',
        'password_confirmation' => 'ClaveSegura123!',
    ], $overrides);
}

it('requires a student enrollment number and stores public registrations as pending', function (): void {
    $this->get(route('register'))
        ->assertOk()
        ->assertSee('Crea tu cuenta SIGA')
        ->assertSee('name="institutional_id"', false);

    $this->post(route('register.store'), registrationPayload(['institutional_id' => '']))
        ->assertSessionHasErrors('institutional_id');

    $this->post(route('register.store'), registrationPayload())
        ->assertRedirect(route('login'))
        ->assertSessionHas('success');

    $user = User::where('email', 'persona@example.test')->firstOrFail();

    expect($user->role)->toBe(User::ROLE_USUARIO)
        ->and($user->status)->toBe('inactivo')
        ->and($user->registration_status)->toBe(User::REGISTRATION_PENDING)
        ->and($user->registration_reviewed_by_id)->toBeNull();

    $this->post(route('login.post'), [
        'login' => $user->email,
        'password' => 'ClaveSegura123!',
    ])->assertSessionHas('error', 'Tu solicitud de registro aún está pendiente de aprobación.');

    $this->assertGuest();
});

it('allows professors to register without an institutional id', function (): void {
    $this->post(route('register.store'), registrationPayload([
        'email' => 'profesor@example.test',
        'user_type' => User::TYPE_DOCENTE,
        'institutional_id' => '',
    ]))->assertRedirect(route('login'));

    $professor = User::where('email', 'profesor@example.test')->firstOrFail();

    expect($professor->user_type)->toBe(User::TYPE_DOCENTE)
        ->and($professor->institutional_id)->toBeNull()
        ->and($professor->registration_status)->toBe(User::REGISTRATION_PENDING);
});

it('allows only administrators supervisors and loan managers to review registrations', function (string $role): void {
    $actor = User::factory()->create(['role' => $role]);

    $this->actingAs($actor)
        ->get(route('users.registrations.index'))
        ->assertOk();
})->with([
    'administrator' => User::ROLE_ADMIN,
    'supervisor' => User::ROLE_SUPERVISOR,
    'loan manager' => User::ROLE_ENCARGADO_PRESTAMOS,
]);

it('denies other roles access to registration approvals', function (string $role): void {
    $actor = User::factory()->create(['role' => $role]);

    $this->actingAs($actor)
        ->get(route('users.registrations.index'))
        ->assertForbidden();
})->with([
    'asset manager' => User::ROLE_ENCARGADO_ACTIVOS,
    'technician' => User::ROLE_TECNICO,
    'requester' => User::ROLE_USUARIO,
]);

it('activates approved accounts and blocks rejected accounts from login', function (): void {
    $supervisor = User::factory()->create(['role' => User::ROLE_SUPERVISOR]);
    $loanManager = User::factory()->create(['role' => User::ROLE_ENCARGADO_PRESTAMOS]);

    $this->post(route('register.store'), registrationPayload([
        'email' => 'approved@example.test',
    ]))->assertRedirect(route('login'));
    $approved = User::where('email', 'approved@example.test')->firstOrFail();

    $this->actingAs($supervisor)
        ->post(route('users.registrations.approve', $approved))
        ->assertRedirect()
        ->assertSessionHas('success');

    $approved->refresh();
    expect($approved->registration_status)->toBe(User::REGISTRATION_APPROVED)
        ->and($approved->status)->toBe('activo')
        ->and($approved->registration_reviewed_by_id)->toBe($supervisor->id)
        ->and($approved->registration_reviewed_at)->not->toBeNull();

    $this->post(route('login.post'), [
        'login' => $approved->email,
        'password' => 'ClaveSegura123!',
    ])->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($approved);

    auth()->logout();
    $this->post(route('register.store'), registrationPayload([
        'email' => 'rejected@example.test',
        'institutional_id' => 'ALU-TEST-101',
    ]))->assertRedirect(route('login'));
    $rejected = User::where('email', 'rejected@example.test')->firstOrFail();

    $this->actingAs($loanManager)
        ->post(route('users.registrations.reject', $rejected))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($rejected->fresh()->registration_status)->toBe(User::REGISTRATION_REJECTED);

    auth()->logout();
    $this->post(route('login.post'), [
        'login' => $rejected->email,
        'password' => 'ClaveSegura123!',
    ])->assertSessionHas('error', 'Tu solicitud de registro no fue aprobada. Contacta al área de préstamos para más información.');

    $this->assertGuest();
});

it('requires an approved requester account to place a public loan request', function (): void {
    $this->post(route('public.catalog.request'), [])
        ->assertRedirect(route('login'));

    $pendingUser = User::factory()->create([
        'status' => 'inactivo',
        'registration_status' => User::REGISTRATION_PENDING,
    ]);

    $this->actingAs($pendingUser)
        ->post(route('public.catalog.request'), [])
        ->assertForbidden();
});

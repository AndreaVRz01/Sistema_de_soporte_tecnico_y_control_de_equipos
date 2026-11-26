<?php

use App\Models\Category;
use App\Models\Equipment;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\User;
use App\Notifications\LoanRejected;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function scheduleTestEquipment(string $code = 'SCH-001'): Equipment
{
    $category = Category::firstOrCreate(
        ['code' => 'TEST'],
        ['name' => 'Pruebas']
    );

    return Equipment::create([
        'category_id' => $category->id,
        'inventory_code' => $code,
        'serial_number' => 'SN-' . $code,
        'name' => 'Equipo horario prueba',
        'brand' => 'Marca',
        'model' => 'Modelo',
        'status' => 'disponible',
        'functional_status' => 'funcional',
        'physical_condition' => 'bueno',
    ]);
}

it('permite negar un préstamo desde la vista de préstamos y muestra el motivo al solicitante', function (): void {
    $requester = User::factory()->create([
        'role' => User::ROLE_USUARIO,
        'status' => 'activo',
        'registration_status' => User::REGISTRATION_APPROVED,
    ]);
    $manager = User::factory()->create(['role' => User::ROLE_ENCARGADO_PRESTAMOS]);
    $equipment = scheduleTestEquipment('NEG-001');
    $equipment->update(['status' => 'reservado']);

    $loan = Loan::create([
        'folio' => 'PR-2026-9001',
        'user_id' => $requester->id,
        'purpose' => 'Práctica de redes',
        'location_of_use' => 'Lab 1',
        'expected_return_date' => now()->next(Carbon::MONDAY)->setTime(12, 0),
        'status' => 'solicitado',
    ]);
    LoanItem::create([
        'loan_id' => $loan->id,
        'equipment_id' => $equipment->id,
        'condition_on_loan' => 'bueno',
    ]);

    $this->actingAs($manager)
        ->get(route('loans.index'))
        ->assertOk()
        ->assertSee('openRejectLoanModal')
        ->assertSee('title="Negar préstamo"', false);

    $reason = 'Equipo requerido para mantenimiento programado.';

    $this->actingAs($manager)
        ->post(route('loans.reject', $loan), ['rejection_reason' => $reason])
        ->assertRedirect();

    expect($loan->fresh()->status)->toBe('rechazado')
        ->and($loan->fresh()->rejection_reason)->toBe($reason)
        ->and($equipment->fresh()->status)->toBe('disponible')
        ->and($requester->notifications()->where('type', LoanRejected::class)->exists())->toBeTrue();

    $this->actingAs($requester)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('PR-2026-9001')
        ->assertSee($reason);

    $this->actingAs($requester)
        ->get(route('portal.activity'))
        ->assertOk()
        ->assertSee('Préstamo rechazado')
        ->assertSee($reason);
});

it('impide negar préstamos que no están en estado solicitado o por roles sin permiso', function (): void {
    $requester = User::factory()->create(['role' => User::ROLE_USUARIO]);
    $technician = User::factory()->create(['role' => User::ROLE_TECNICO]);
    $manager = User::factory()->create(['role' => User::ROLE_ENCARGADO_PRESTAMOS]);

    $loan = Loan::create([
        'folio' => 'PR-2026-9002',
        'user_id' => $requester->id,
        'purpose' => 'Práctica',
        'expected_return_date' => now()->next(Carbon::MONDAY)->setTime(12, 0),
        'status' => 'entregado',
    ]);

    $this->actingAs($technician)
        ->post(route('loans.reject', $loan), ['rejection_reason' => 'Sin permiso'])
        ->assertForbidden();

    $this->actingAs($manager)
        ->post(route('loans.reject', $loan), ['rejection_reason' => 'Ya entregado'])
        ->assertSessionHas('error');

    expect($loan->fresh()->status)->toBe('entregado');
});

it('bloquea días pasados, domingos y horarios fuera de rango en préstamos', function (string $dateExpression, bool $shouldPass): void {
    Carbon::setTestNow(Carbon::create(2026, 11, 26, 10, 0, 0, config('app.timezone')));

    $manager = User::factory()->create(['role' => User::ROLE_ENCARGADO_PRESTAMOS]);
    $requester = User::factory()->create(['role' => User::ROLE_USUARIO, 'status' => 'activo']);
    $equipment = scheduleTestEquipment('SCH-' . uniqid());

    $response = $this->actingAs($manager)->post(route('loans.store'), [
        'user_id' => $requester->id,
        'equipment_ids' => [$equipment->id],
        'purpose' => 'Validación de horario',
        'location_of_use' => 'Aula 4',
        'expected_return_date' => $dateExpression,
    ]);

    if ($shouldPass) {
        $response->assertSessionDoesntHaveErrors('expected_return_date');
    } else {
        $response->assertSessionHasErrors('expected_return_date');
    }

    Carbon::setTestNow();
})->with([
    'Día pasado (25 nov)' => ['2026-11-25T10:00', false],
    'Domingo inhábil (29 nov)' => ['2026-11-29T10:00', false],
    'Martes antes de 07:00 (06:30)' => ['2026-12-01T06:30', false],
    'Martes después de 20:00 (21:00)' => ['2026-12-01T21:00', false],
    'Martes límite inferior (07:00)' => ['2026-12-01T07:00', true],
    'Martes límite superior (20:00)' => ['2026-12-01T20:00', true],
    'Sábado antes de 09:00 (08:30)' => ['2026-11-28T08:30', false],
    'Sábado después de 13:00 (15:00)' => ['2026-11-28T15:00', false],
    'Sábado límite inferior (09:00)' => ['2026-11-28T09:00', true],
    'Sábado límite superior (13:00)' => ['2026-11-28T13:00', true],
]);

it('bloquea días pasados y domingos en sanciones y adquisición de activos', function (): void {
    Carbon::setTestNow(Carbon::create(2026, 11, 26, 10, 0, 0, config('app.timezone')));

    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $student = User::factory()->create(['role' => User::ROLE_USUARIO, 'status' => 'activo']);
    $category = Category::create(['name' => 'Cómputo', 'code' => 'CMP']);

    // Sanción con fecha pasada y domingo
    $this->actingAs($admin)->post(route('sanctions.store'), [
        'user_id' => $student->id,
        'sanction_type' => 'suspension_temporal',
        'reason' => 'Prueba fecha pasada',
        'start_date' => '2026-11-25',
        'end_date' => '2026-11-29',
    ])->assertSessionHasErrors(['start_date', 'end_date']);

    // Activo con fecha pasada y domingo
    $this->actingAs($admin)->post(route('equipment.store'), [
        'category_id' => $category->id,
        'name' => 'Laptop prueba',
        'brand' => 'Dell',
        'model' => 'Latitude',
        'serial_number' => 'SN-DATE-001',
        'physical_condition' => 'bueno',
        'location' => 'Lab A',
        'acquisition_date' => '2026-11-29',
    ])->assertSessionHasErrors(['acquisition_date']);

    Carbon::setTestNow();
});

it('valida las mismas restricciones de fecha y horario en solicitudes del catálogo público', function (): void {
    Carbon::setTestNow(Carbon::create(2026, 11, 26, 10, 0, 0, config('app.timezone')));

    $requester = User::factory()->create([
        'role' => User::ROLE_USUARIO,
        'status' => 'activo',
        'registration_status' => User::REGISTRATION_APPROVED,
    ]);
    $equipment = scheduleTestEquipment('PUB-001');

    // Domingo bloqueado
    $this->actingAs($requester)->post(route('public.catalog.request'), [
        'equipment_id' => $equipment->id,
        'purpose' => 'Práctica',
        'location_of_use' => 'Aula 2',
        'expected_return_date' => '2026-11-29T10:00',
    ])->assertSessionHasErrors('expected_return_date');

    // Sábado 15:00 bloqueado
    $this->actingAs($requester)->post(route('public.catalog.request'), [
        'equipment_id' => $equipment->id,
        'purpose' => 'Práctica',
        'location_of_use' => 'Aula 2',
        'expected_return_date' => '2026-11-28T15:00',
    ])->assertSessionHasErrors('expected_return_date');

    // Sábado 11:30 permitido
    $this->actingAs($requester)->post(route('public.catalog.request'), [
        'equipment_id' => $equipment->id,
        'purpose' => 'Práctica válida',
        'location_of_use' => 'Aula 2',
        'expected_return_date' => '2026-11-28T11:30',
    ])->assertRedirect(route('public.catalog'));

    Carbon::setTestNow();
});

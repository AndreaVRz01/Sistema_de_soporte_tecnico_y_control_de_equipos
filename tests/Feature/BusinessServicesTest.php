<?php

use App\Models\Category;
use App\Models\Equipment;
use App\Models\Incident;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Sanction;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\LoanService;
use App\Services\PaymentTicketService;
use App\Services\ServiceTicketService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function businessTestEquipment(Category $category, string $code): Equipment
{
    return Equipment::create([
        'category_id' => $category->id,
        'inventory_code' => $code,
        'serial_number' => 'SER-'.$code,
        'name' => 'Equipo de prueba',
        'brand' => 'Marca',
        'model' => 'Modelo',
        'status' => 'disponible',
        'functional_status' => 'funcional',
        'physical_condition' => 'bueno',
    ]);
}

it('bloquea préstamos al superar cuatro penalizaciones no canceladas', function (): void {
    $user = User::factory()->create(['role' => User::ROLE_USUARIO]);
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    foreach (range(1, 5) as $number) {
        Sanction::create([
            'user_id' => $user->id,
            'applied_by_id' => $admin->id,
            'sanction_type' => 'amonestacion_escrita',
            'reason' => "Penalización {$number}",
            'start_date' => today(),
            'status' => 'cumplida',
        ]);
    }

    expect($user->fresh()->canBorrow())->toBeFalse();
});

it('requiere supervisor para aprobar solicitudes con más de diez equipos', function (): void {
    $requester = User::factory()->create(['role' => User::ROLE_USUARIO]);
    $loanManager = User::factory()->create(['role' => User::ROLE_ENCARGADO_PRESTAMOS]);
    $supervisor = User::factory()->create(['role' => User::ROLE_SUPERVISOR]);
    $category = Category::create(['name' => 'Prueba', 'code' => 'TEST']);
    $equipmentIds = [];

    foreach (range(1, 11) as $number) {
        $equipmentIds[] = businessTestEquipment($category, "TEST-{$number}")->id;
    }

    $activityLog = new ActivityLogService();
    $service = new LoanService($activityLog, new PaymentTicketService($activityLog));
    $loan = $service->requestLoan($requester, $equipmentIds, 'Evento de prueba', now()->addDays(2)->toDateTimeString());

    expect($loan->requires_supervisor_approval)->toBeTrue()
        ->and($supervisor->notifications()->count())->toBe(1);

    expect(fn () => $service->approve($loanManager, $loan))
        ->toThrow(AuthorizationException::class);

    expect($service->approve($supervisor, $loan)->supervisor_approved_by_id)->toBe($supervisor->id);
});

it('penaliza una devolución dañada y crea un ticket técnico', function (): void {
    $requester = User::factory()->create(['role' => User::ROLE_USUARIO]);
    $receiver = User::factory()->create(['role' => User::ROLE_ENCARGADO_PRESTAMOS]);
    $category = Category::create(['name' => 'Prueba', 'code' => 'TEST']);
    $equipment = businessTestEquipment($category, 'TEST-DAMAGE');
    $secondEquipment = businessTestEquipment($category, 'TEST-DAMAGE-2');
    $loan = Loan::create([
        'folio' => 'PRE-TEST-DAMAGE',
        'user_id' => $requester->id,
        'purpose' => 'Prueba de devolución',
        'request_date' => now()->subDays(3),
        'expected_return_date' => now()->subDay(),
        'status' => 'entregado',
    ]);
    LoanItem::create([
        'loan_id' => $loan->id,
        'equipment_id' => $equipment->id,
        'condition_on_loan' => 'bueno',
    ]);
    LoanItem::create([
        'loan_id' => $loan->id,
        'equipment_id' => $secondEquipment->id,
        'condition_on_loan' => 'bueno',
    ]);

    $activityLog = new ActivityLogService();
    $service = new LoanService($activityLog, new PaymentTicketService($activityLog));
    $service->registerReturn($receiver, $loan, [[
        'equipment_id' => $equipment->id,
        'condition_on_return' => 'dañado',
        'notes_on_return' => 'Pantalla rota',
        'amount_to_pay' => 100,
    ], [
        'equipment_id' => $secondEquipment->id,
        'condition_on_return' => 'incompleto',
        'notes_on_return' => 'Falta el cargador',
    ]]);

    expect(Incident::query()->whereIn('loan_item_id', $loan->items()->pluck('id'))->count())->toBe(2)
        ->and($requester->sanctions()->count())->toBe(3)
        ->and($equipment->fresh()->status)->toBe('en_reparacion');
});

it('marca los préstamos vencidos y aplica la penalización automática una sola vez', function (): void {
    $requester = User::factory()->create(['role' => User::ROLE_USUARIO]);
    $loan = Loan::create([
        'folio' => 'PRE-TEST-OVERDUE',
        'user_id' => $requester->id,
        'purpose' => 'Prueba de vencimiento',
        'request_date' => now()->subDays(3),
        'expected_return_date' => now()->subDay(),
        'status' => 'entregado',
    ]);
    $activityLog = new ActivityLogService();
    $service = new LoanService($activityLog, new PaymentTicketService($activityLog));

    expect($service->markOverdueLoans())->toBe(1)
        ->and($service->markOverdueLoans())->toBe(0)
        ->and($loan->fresh()->status)->toBe('vencido')
        ->and($requester->sanctions()->first()->is_automatic)->toBeTrue();
});

it('crea un ticket de pago y bloquea al usuario por un activo no devuelto', function (): void {
    $requester = User::factory()->create(['role' => User::ROLE_USUARIO]);
    $loanManager = User::factory()->create(['role' => User::ROLE_ENCARGADO_PRESTAMOS]);
    $category = Category::create(['name' => 'Prueba', 'code' => 'TEST']);
    $equipment = businessTestEquipment($category, 'TEST-NONRETURN');
    $loan = Loan::create([
        'folio' => 'PRE-TEST-NONRETURN',
        'user_id' => $requester->id,
        'purpose' => 'Prueba de no devolución',
        'request_date' => now()->subDays(3),
        'expected_return_date' => now()->subDay(),
        'status' => 'vencido',
    ]);
    LoanItem::create([
        'loan_id' => $loan->id,
        'equipment_id' => $equipment->id,
        'condition_on_loan' => 'bueno',
    ]);

    $service = new PaymentTicketService(new ActivityLogService());
    $ticket = $service->reportNonReturn($loanManager, $loan, $equipment->id, 250, 'Equipo no devuelto');

    expect($ticket->status)->toBe('pendiente')
        ->and($requester->fresh()->canBorrow())->toBeFalse()
        ->and($equipment->fresh()->status)->toBe('baja')
        ->and($requester->sanctions()->where('sanction_type', 'bloqueo_definitivo')->exists())->toBeTrue();

    $service->markPaid($loanManager, $ticket);

    expect($requester->sanctions()->where('sanction_type', 'bloqueo_definitivo')->first()->is_paid)->toBeTrue()
        ->and($requester->fresh()->canBorrow())->toBeFalse();
});

it('permite al técnico asignado comentar y resolver el ticket', function (): void {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $technician = User::factory()->create(['role' => User::ROLE_TECNICO]);
    $category = Category::create(['name' => 'Prueba', 'code' => 'TEST']);
    $equipment = businessTestEquipment($category, 'TEST-SERVICE');
    $service = new ServiceTicketService(new ActivityLogService());

    $ticket = $service->createTicket($admin, [
        'equipment_id' => $equipment->id,
        'title' => 'Mantenimiento preventivo',
        'description' => 'Revisión anual',
        'type' => 'mantenimiento_preventivo',
    ]);
    $ticket = $service->assignTechnician($admin, $ticket, $technician);
    $service->addComment($technician, $ticket, 'Se inició el diagnóstico.');
    $ticket = $service->updateStatus($technician, $ticket, [
        'status' => 'resuelto',
        'solution_applied' => 'Limpieza y actualización de firmware.',
        'functional_status' => 'funcional',
    ]);

    expect($ticket->status)->toBe('resuelto')
        ->and($ticket->comments()->count())->toBe(1)
        ->and($ticket->logs()->count())->toBe(1)
        ->and($equipment->fresh()->status)->toBe('disponible');
});
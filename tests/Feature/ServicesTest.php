<?php

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\AuthenticationService;
use App\Services\EquipmentService;
use App\Services\LoanService;
use App\Services\PaymentTicketService;
use App\Services\PasswordRecoveryService;
use App\Services\UserManagementService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

it('registra eventos y metadatos en la bitácora', function (): void {
    $actor = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $category = Category::create(['name' => 'Bitácora', 'code' => 'LOG']);
    $equipment = Equipment::create([
        'category_id' => $category->id,
        'inventory_code' => 'LOG-001',
        'serial_number' => 'LOG-SN-001',
        'name' => 'Equipo de bitácora',
        'brand' => 'Marca',
        'model' => 'Modelo',
    ]);

    $entry = (new ActivityLogService())->record($actor, 'inventario.consultado', $equipment, ['source' => 'test']);

    expect($entry)->toBeInstanceOf(ActivityLog::class)
        ->and($entry->user_id)->toBe($actor->id)
        ->and($entry->subject_id)->toBe($equipment->id)
        ->and($entry->metadata)->toBe(['source' => 'test']);
});

it('inicia y cierra sesión registrando ambos eventos', function (): void {
    $user = User::factory()->create([
        'role' => User::ROLE_USUARIO,
        'password' => Hash::make('Correcta123!'),
    ]);
    $request = Request::create('/login', 'POST');
    $request->setLaravelSession(app('session')->driver());
    $request->setUserResolver(fn () => Auth::user());

    $service = new AuthenticationService(new ActivityLogService());
    $authenticatedUser = $service->login($request, [
        'email' => $user->email,
        'password' => 'Correcta123!',
    ]);

    expect($authenticatedUser->is($user))->toBeTrue()
        ->and(Auth::check())->toBeTrue();

    $service->logout($request);

    expect(Auth::check())->toBeFalse()
        ->and(ActivityLog::query()->whereIn('event', ['sesion.iniciada', 'sesion.cerrada'])->count())->toBe(2);
});

it('permite al encargado de activos crear, actualizar y cambiar el estado', function (): void {
    $actor = User::factory()->create(['role' => User::ROLE_ENCARGADO_ACTIVOS]);
    $category = Category::create(['name' => 'Inventario', 'code' => 'INV']);
    $service = new EquipmentService(new ActivityLogService());

    $equipment = $service->create($actor, [
        'category_id' => $category->id,
        'inventory_code' => 'INV-001',
        'serial_number' => 'INV-SN-001',
        'name' => 'Laptop de prueba',
        'brand' => 'Marca',
        'model' => 'Modelo',
        'warranty_until' => now()->addYear()->toDateString(),
        'warranty_details' => 'Garantía del fabricante',
    ]);
    $service->update($actor, $equipment, ['name' => 'Laptop actualizada']);
    $equipment = $service->setStatus($actor, $equipment, 'mantenimiento', 'parcialmente_funcional');

    expect($equipment->name)->toBe('Laptop actualizada')
        ->and($equipment->status)->toBe('mantenimiento')
        ->and($equipment->functional_status)->toBe('parcialmente_funcional')
        ->and($equipment->warranty_details)->toBe('Garantía del fabricante');
});

it('rechaza solicitudes liberando reservas y entrega préstamos aprobados', function (): void {
    $requester = User::factory()->create(['role' => User::ROLE_USUARIO]);
    $manager = User::factory()->create(['role' => User::ROLE_ENCARGADO_PRESTAMOS]);
    $category = Category::create(['name' => 'Préstamos', 'code' => 'LOAN']);
    $rejectedEquipment = Equipment::create([
        'category_id' => $category->id,
        'inventory_code' => 'LOAN-001',
        'serial_number' => 'LOAN-SN-001',
        'name' => 'Activo rechazado',
        'brand' => 'Marca',
        'model' => 'Modelo',
        'status' => 'reservado',
    ]);
    $rejectedLoan = Loan::create([
        'folio' => 'PRE-TEST-REJECT',
        'user_id' => $requester->id,
        'purpose' => 'Prueba de rechazo',
        'expected_return_date' => now()->addDays(2),
        'status' => 'solicitado',
    ]);
    LoanItem::create([
        'loan_id' => $rejectedLoan->id,
        'equipment_id' => $rejectedEquipment->id,
        'condition_on_loan' => 'bueno',
    ]);

    $dispatchEquipment = Equipment::create([
        'category_id' => $category->id,
        'inventory_code' => 'LOAN-002',
        'serial_number' => 'LOAN-SN-002',
        'name' => 'Activo aprobado',
        'brand' => 'Marca',
        'model' => 'Modelo',
        'status' => 'reservado',
    ]);
    $approvedLoan = Loan::create([
        'folio' => 'PRE-TEST-DISPATCH',
        'user_id' => $requester->id,
        'purpose' => 'Prueba de entrega',
        'expected_return_date' => now()->addDays(2),
        'status' => 'solicitado',
    ]);
    LoanItem::create([
        'loan_id' => $approvedLoan->id,
        'equipment_id' => $dispatchEquipment->id,
        'condition_on_loan' => 'bueno',
    ]);

    $activityLog = new ActivityLogService();
    $service = new LoanService($activityLog, new PaymentTicketService($activityLog));
    $service->reject($manager, $rejectedLoan, 'No hay disponibilidad para esas fechas.');
    $service->approve($manager, $approvedLoan);
    $deliveredLoan = $service->dispatch($manager, $approvedLoan);

    expect($rejectedLoan->fresh()->status)->toBe('rechazado')
        ->and($rejectedEquipment->fresh()->status)->toBe('disponible')
        ->and($deliveredLoan->status)->toBe('entregado')
        ->and($dispatchEquipment->fresh()->status)->toBe('prestado');
});

it('envía y completa la recuperación de contraseña con el token emitido', function (): void {
    Notification::fake();
    $user = User::factory()->create(['email' => 'recuperacion@example.test']);
    $service = new PasswordRecoveryService();

    expect($service->sendResetLink($user->email))->toBe(Password::RESET_LINK_SENT);

    $token = null;
    Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token): bool {
        $token = $notification->token;

        return true;
    });

    expect($token)->toBeString();
    expect($service->resetPassword([
        'email' => $user->email,
        'token' => $token,
        'password' => 'NuevaClave123!',
        'password_confirmation' => 'NuevaClave123!',
    ]))->toBe(Password::PASSWORD_RESET);
    expect(Hash::check('NuevaClave123!', $user->fresh()->password))->toBeTrue();
});

it('permite al administrador crear personal y al encargado crear solicitantes', function (): void {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $loanManager = User::factory()->create(['role' => User::ROLE_ENCARGADO_PRESTAMOS]);
    $service = new UserManagementService(new ActivityLogService());

    $staff = $service->registerStaff($admin, [
        'name' => 'Técnico de prueba',
        'email' => 'tecnico@example.test',
        'password' => 'ClaveTemporal123!',
        'role' => User::ROLE_TECNICO,
        'user_type' => User::TYPE_ADMINISTRATIVO,
        'institutional_id' => 'EMP-TEST-001',
    ]);
    $requester = $service->registerRequester($loanManager, [
        'name' => 'Solicitante de prueba',
        'email' => 'solicitante@example.test',
        'password' => 'ClaveTemporal123!',
        'user_type' => User::TYPE_ALUMNO,
        'institutional_id' => 'ALU-TEST-001',
    ]);

    expect($staff->role)->toBe(User::ROLE_TECNICO)
        ->and($requester->role)->toBe(User::ROLE_USUARIO)
        ->and(Hash::check('ClaveTemporal123!', $requester->password))->toBeTrue()
        ->and(ActivityLog::query()->whereIn('event', ['usuario.personal_creado', 'usuario.solicitante_creado'])->count())->toBe(2);
});
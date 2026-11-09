<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\PublicCatalogController;
use App\Http\Controllers\SanctionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - SIGA Enterprise
|--------------------------------------------------------------------------
| Rutas del Portal Público Institucional y de la Consola Operativa
| Basado en las especificaciones de docs/stitch_asset_loan_maintenance_system
| y la memoria de trabajo técnica.
|--------------------------------------------------------------------------
*/

// =========================================================================
// 1. PORTAL PÚBLICO: CATÁLOGO DE ACTIVOS Y PRÉSTAMOS ESTUDIANTILES
// =========================================================================
Route::get('/', [PublicCatalogController::class, 'index'])->name('public.home');
Route::get('/catalogo', [PublicCatalogController::class, 'index'])->name('public.catalog');
Route::get('/catalogo/activo/{equipment}', [PublicCatalogController::class, 'show'])->name('public.catalog.show');
Route::post('/catalogo/solicitar', [PublicCatalogController::class, 'requestLoan'])->name('public.catalog.request');

// =========================================================================
// 2. AUTENTICACIÓN Y RECUPERACIÓN CON TOKEN
// =========================================================================
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout.get'); // Accesibilidad directa
Route::post('/recuperar-password', [AuthController::class, 'sendRecoveryToken'])->name('password.token');
Route::post('/restablecer-password', [AuthController::class, 'resetPassword'])->name('password.reset');

// =========================================================================
// 3. CONSOLA OPERATIVA PRIVADA (DASHBOARD & MÓDULOS DE GESTIÓN)
// =========================================================================
Route::middleware('auth')->group(function () {
    Route::middleware('role:admin,supervisor,encargado_activos,encargado_prestamos')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });

    Route::prefix('activos')->name('equipment.')->middleware('role:admin,supervisor,encargado_activos')->group(function () {
        Route::get('/', [EquipmentController::class, 'index'])->name('index');
        Route::get('/exportar/csv', [EquipmentController::class, 'export'])->name('export');
        Route::post('/', [EquipmentController::class, 'store'])->name('store');
        Route::get('/{equipment}', [EquipmentController::class, 'show'])->name('show');
        Route::put('/{equipment}', [EquipmentController::class, 'update'])->name('update');
        Route::delete('/{equipment}', [EquipmentController::class, 'destroy'])
            ->middleware('role:admin,encargado_activos')->name('destroy');
    });

    Route::prefix('prestamos')->name('loans.')->middleware('role:admin,supervisor,encargado_prestamos')->group(function () {
        Route::get('/', [LoanController::class, 'index'])->name('index');
        Route::post('/', [LoanController::class, 'store'])->name('store');
        Route::get('/solicitantes/crear', [UserController::class, 'createBorrower'])
            ->middleware('role:encargado_prestamos')->name('borrowers.create');
        Route::post('/solicitantes', [UserController::class, 'storeBorrower'])
            ->middleware('role:encargado_prestamos')->name('borrowers.store');
        Route::get('/{loan}', [LoanController::class, 'show'])->name('show');
        Route::post('/{loan}/aprobar', [LoanController::class, 'approve'])->name('approve');
        Route::post('/{loan}/rechazar', [LoanController::class, 'reject'])->name('reject');
        Route::post('/{loan}/despachar', [LoanController::class, 'dispatch'])->name('dispatch');
        Route::post('/{loan}/devolucion', [LoanController::class, 'receiveReturn'])->name('return');
    });

    Route::prefix('tickets')->name('incidents.')->middleware('role:admin,supervisor,encargado_activos,tecnico')->group(function () {
        Route::get('/', [IncidentController::class, 'index'])->name('index');
        Route::post('/', [IncidentController::class, 'store'])
            ->middleware('role:admin,supervisor,encargado_activos')->name('store');
        Route::get('/{incident}', [IncidentController::class, 'show'])->name('show');
        Route::post('/{incident}/asignar', [IncidentController::class, 'assignTechnician'])
            ->middleware('role:admin,supervisor,encargado_activos')->name('assign');
        Route::post('/{incident}/avance', [IncidentController::class, 'addProgressLog'])->name('log');
        Route::post('/{incident}/resolver', [IncidentController::class, 'resolve'])->name('resolve');
    });

    Route::prefix('usuarios')->name('users.')->middleware('role:admin')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::post('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{user}/reset-credentials', [UserController::class, 'resetCredentials'])->name('reset-credentials');
    });

    Route::prefix('bitacora')->name('audit.')->middleware('role:admin,supervisor')->group(function () {
        Route::get('/', [AuditController::class, 'index'])->name('index');
        Route::get('/exportar', [AuditController::class, 'export'])->name('export');
        Route::get('/{id}', [AuditController::class, 'show'])->name('show');
    });

    Route::prefix('sanciones')->name('sanctions.')->middleware('role:admin,supervisor,encargado_activos')->group(function () {
        Route::get('/', [SanctionController::class, 'index'])->name('index');
        Route::post('/', [SanctionController::class, 'store'])->name('store');
        Route::post('/{sanction}/resolver', [SanctionController::class, 'resolve'])->name('resolve');
    });
});

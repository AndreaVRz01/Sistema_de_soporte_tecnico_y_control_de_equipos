<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\LoanService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('loans:mark-overdue', function (LoanService $loans) {
    $count = $loans->markOverdueLoans();
    $this->info("Se marcaron {$count} préstamos vencidos.");
})->purpose('Marcar préstamos vencidos y generar sus penalizaciones');

Schedule::command('loans:mark-overdue')->hourly()->withoutOverlapping();

<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\Incident;
use App\Models\Loan;
use App\Models\Sanction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Muestra el panel de control principal de operaciones y supervisión.
     * Basado en: docs/stitch_asset_loan_maintenance_system/dashboard_principal_neum_rfico
     */
    public function index(Request $request): View
    {
        // 1. Métricas Principales (KPIs)
        $totalEquipment = Equipment::count();
        $availableEquipment = Equipment::where('status', 'disponible')->count();
        $activeLoansCount = Loan::where('status', 'entregado')->count();
        $inMaintenanceCount = Equipment::where('status', 'en_mantenimiento')->count();
        $overdueLoansCount = Loan::where('status', 'entregado')
            ->where('expected_return_date', '<', now())
            ->count();
        $activeSanctionsCount = Sanction::where('status', 'activa')->count();
        $openIncidentsCount = Incident::whereNotIn('status', ['resuelto', 'cancelado'])->count();

        // 2. Alertas Críticas Operativas (Próximos a Vencer < 24h y Ya Vencidos)
        $criticalAlertLoans = Loan::with(['user', 'items.equipment'])
            ->where('status', 'entregado')
            ->where(function ($query) {
                $query->where('expected_return_date', '<', now())
                      ->orWhereBetween('expected_return_date', [now(), now()->addHours(24)]);
            })
            ->orderBy('expected_return_date', 'asc')
            ->take(6)
            ->get();

        // 3. Distribución de Equipos por Categoría y Ubicación Física
        $categoriesStats = Category::withCount(['equipment', 'availableEquipment'])
            ->get();

        $locationsStats = Equipment::select('location', DB::raw('count(*) as total'))
            ->groupBy('location')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        // 4. Últimos Movimientos para la Bitácora Resumida en Tiempo Real (Seguro ante migraciones pendientes)
        $recentAuditStream = Schema::hasTable('audit_logs')
            ? AuditLog::with('user')->latest()->take(8)->get()
            : collect();

        // 5. Préstamos Activos Recientes
        $recentLoans = Loan::with(['user', 'items.equipment', 'authorizedBy'])
            ->latest()
            ->take(5)
            ->get();

        // 6. Tickets de Taller Activos
        $activeWorkshopTickets = Incident::with(['equipment', 'technician'])
            ->whereNotIn('status', ['resuelto', 'cancelado'])
            ->orderByRaw("CASE priority WHEN 'critica' THEN 1 WHEN 'alta' THEN 2 WHEN 'media' THEN 3 ELSE 4 END")
            ->take(5)
            ->get();

        return view('private.dashboard', compact(
            'totalEquipment',
            'availableEquipment',
            'activeLoansCount',
            'inMaintenanceCount',
            'overdueLoansCount',
            'activeSanctionsCount',
            'openIncidentsCount',
            'criticalAlertLoans',
            'categoriesStats',
            'locationsStats',
            'recentAuditStream',
            'recentLoans',
            'activeWorkshopTickets'
        ));
    }
}


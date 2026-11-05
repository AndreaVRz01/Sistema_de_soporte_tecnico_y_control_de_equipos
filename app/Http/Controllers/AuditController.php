<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditController extends Controller
{
    /**
     * Muestra la bitácora y auditoría de movimientos en tiempo real.
     * Basado en: docs/stitch_asset_loan_maintenance_system/bit_cora_y_auditor_a_de_movimientos_en_tiempo_real
     * y bit_cora_con_modal_de_exportaci_n_y_reporte_forense_avanzado
     */
    public function index(Request $request): View
    {
        if (!Schema::hasTable('audit_logs')) {
            $counts = ['total' => 0, 'critico' => 0, 'advertencia' => 0, 'normal' => 0];
            $logs = new LengthAwarePaginator([], 0, 12);
            $operators = collect();
            return view('private.audit.index', compact('logs', 'counts', 'operators'));
        }

        $query = AuditLog::with('user');

        // 1. Filtro por Nivel de Riesgo Forense
        if ($risk = $request->input('risk_level')) {
            $query->where('risk_level', $risk);
        }

        // 2. Filtro por Entidad ER
        if ($entity = $request->input('entity_type')) {
            $query->where('entity_type', $entity);
        }

        // 3. Filtro por Acción u Operación
        if ($action = $request->input('action')) {
            $query->where('action', 'like', "%{$action}%");
        }

        // 4. Filtro por Operador
        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        // 5. Ventana Temporal
        if ($timeframe = $request->input('timeframe')) {
            if ($timeframe === '24h') {
                $query->where('created_at', '>=', now()->subDay());
            } elseif ($timeframe === '7d') {
                $query->where('created_at', '>=', now()->subDays(7));
            } elseif ($timeframe === '30d') {
                $query->where('created_at', '>=', now()->subDays(30));
            }
        }

        if ($from = $request->input('from_date')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->input('to_date')) {
            $query->whereDate('created_at', '<=', $to);
        }

        // 6. Búsqueda por texto (Folio, Descripción, IP o Nombre)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('event_folio', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Conteo rápido
        $counts = [
            'total' => AuditLog::count(),
            'critico' => AuditLog::where('risk_level', 'critico')->count(),
            'advertencia' => AuditLog::where('risk_level', 'advertencia')->count(),
            'normal' => AuditLog::where('risk_level', 'normal')->count(),
        ];

        $logs = $query->latest()->paginate(12)->withQueryString();
        $operators = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_SUPERVISOR, User::ROLE_ENCARGADO_ACTIVOS, User::ROLE_ENCARGADO_PRESTAMOS, User::ROLE_TECNICO])
            ->orderBy('name')
            ->get();

        return view('private.audit.index', compact('logs', 'counts', 'operators'));
    }

    /**
     * Muestra la inspección forense detallada de un evento con su hash y diff.
     */
    public function show($id): JsonResponse
    {
        $log = AuditLog::with('user')->findOrFail($id);

        $integrityHash = hash('sha256', $log->event_folio . $log->created_at . $log->action . $log->ip_address . json_encode($log->diff_payload));

        return response()->json([
            'log' => $log,
            'integrity_hash' => $integrityHash,
            'timestamp_ms' => $log->created_at->getTimestampMs(),
        ]);
    }

    /**
     * Exportación forense avanzada (CSV, JSON o reporte imprimible).
     * Satisface el modal de exportación de: bit_cora_con_modal_de_exportaci_n_y_reporte_forense_avanzado
     */
    public function export(Request $request): StreamedResponse|JsonResponse|View
    {
        $query = AuditLog::with('user');

        // Aplicar filtros de la ventana de exportación
        if ($timeframe = $request->input('timeframe')) {
            if ($timeframe === '24h') {
                $query->where('created_at', '>=', now()->subDay());
            } elseif ($timeframe === '7d') {
                $query->where('created_at', '>=', now()->subDays(7));
            }
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($riskLevels = $request->input('risk_levels')) {
            if (is_array($riskLevels)) {
                $query->whereIn('risk_level', $riskLevels);
            }
        }

        if ($entities = $request->input('entities')) {
            if (is_array($entities)) {
                $query->whereIn('entity_type', $entities);
            }
        }

        $format = $request->input('export_format', 'csv');
        $logs = $query->latest()->get();

        // 1. Exportación en JSON
        if ($format === 'json') {
            return response()->json([
                'audit_report_id' => 'REP-' . date('Ymd-His'),
                'generated_at' => now()->toIso8601String(),
                'total_events' => $logs->count(),
                'events' => $logs,
            ]);
        }

        // 2. Reporte Imprimible / Vista HTML
        if ($format === 'print') {
            return view('private.audit.report', compact('logs'));
        }

        // 3. Exportación en CSV (Por Defecto)
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="auditoria_forense_siga_' . date('Ymd_His') . '.csv"',
        ];

        return response()->stream(function () use ($logs) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
            fputcsv($handle, ['Folio Evento', 'Timestamp (UTC)', 'Entidad ER', 'Operación', 'Operador', 'IP Origen', 'Riesgo', 'Descripción']);

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->event_folio,
                    $log->created_at->toIso8601String(),
                    $log->entity_type,
                    $log->action,
                    $log->user->name ?? 'Sistema / Externo',
                    $log->ip_address,
                    strtoupper($log->risk_level),
                    $log->description,
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }
}


<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class AuditService
{
    /**
     * Registra un movimiento o evento en la bitácora de auditoría forense.
     */
    public static function log(
        string $entityType,
        ?int $entityId,
        string $action,
        string $description,
        ?array $diff = null,
        string $riskLevel = 'normal',
        ?int $userId = null
    ): ?AuditLog {
        try {
            if (!Schema::hasTable('audit_logs')) {
                return null;
            }

            $folio = 'EVT-' . date('Y') . '-' . strtoupper(Str::random(6));

            return AuditLog::create([
                'event_folio' => $folio,
                'user_id' => $userId ?? Auth::id(),
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'action' => strtoupper($action),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => substr(request()->userAgent() ?? 'Sistema / Terminal', 0, 500),
                'description' => $description,
                'diff_payload' => $diff,
                'risk_level' => in_array($riskLevel, ['normal', 'advertencia', 'critico']) ? $riskLevel : 'normal',
            ]);
        } catch (Throwable $e) {
            // No romper la transacción principal si la bitácora falla
            report($e);
            return null;
        }
    }
}


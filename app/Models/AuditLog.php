<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_folio',
        'user_id',
        'entity_type',
        'entity_id',
        'action',
        'ip_address',
        'user_agent',
        'description',
        'diff_payload',
        'risk_level',
    ];

    protected function casts(): array
    {
        return [
            'diff_payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeCritical(Builder $query): Builder
    {
        return $query->where('risk_level', 'critico');
    }

    public function scopeWarning(Builder $query): Builder
    {
        return $query->where('risk_level', 'advertencia');
    }

    public function scopeRecent(Builder $query, int $limit = 10): Builder
    {
        return $query->latest()->limit($limit);
    }
}


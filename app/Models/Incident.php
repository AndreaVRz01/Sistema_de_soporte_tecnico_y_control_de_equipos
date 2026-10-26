<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Incident extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_code',
        'equipment_id',
        'loan_item_id',
        'reported_by_id',
        'assigned_technician_id',
        'title',
        'description',
        'type',
        'priority',
        'status',
        'diagnostic',
        'solution_applied',
        'estimated_cost',
        'actual_cost',
        'reported_at',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'reported_at' => 'datetime',
            'resolved_at' => 'datetime',
            'estimated_cost' => 'decimal:2',
            'actual_cost' => 'decimal:2',
        ];
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function loanItem(): BelongsTo
    {
        return $this->belongsTo(LoanItem::class);
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_technician_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(IncidentLog::class);
    }

    public function sanction(): HasOne
    {
        return $this->hasOne(Sanction::class);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['resuelto', 'cancelado']);
    }

    public function scopeAssignedTo(Builder $query, int $technicianId): Builder
    {
        return $query->where('assigned_technician_id', $technicianId);
    }
}

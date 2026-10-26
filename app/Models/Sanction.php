<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sanction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'loan_id',
        'incident_id',
        'applied_by_id',
        'sanction_type',
        'reason',
        'start_date',
        'end_date',
        'amount_to_pay',
        'is_paid',
        'status',
        'resolution_notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'amount_to_pay' => 'decimal:2',
            'is_paid' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function appliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by_id');
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'activa');
    }
}

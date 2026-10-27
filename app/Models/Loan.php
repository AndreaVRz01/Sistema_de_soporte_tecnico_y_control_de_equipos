<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'folio',
        'user_id',
        'authorized_by_id',
        'dispatched_by_id',
        'received_by_id',
        'purpose',
        'location_of_use',
        'request_date',
        'loan_date',
        'expected_return_date',
        'actual_return_date',
        'status',
        'rejection_reason',
        'observations',
    ];

    protected function casts(): array
    {
        return [
            'request_date' => 'datetime',
            'loan_date' => 'datetime',
            'expected_return_date' => 'datetime',
            'actual_return_date' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function authorizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by_id');
    }

    public function dispatchedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(LoanItem::class);
    }

    public function sanctions(): HasMany
    {
        return $this->hasMany(Sanction::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'solicitado';
    }

    public function isOverdue(): bool
    {
        return in_array($this->status, ['aprobado', 'entregado']) &&
               now()->greaterThan($this->expected_return_date);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'solicitado');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'entregado');
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', 'entregado')
                     ->where('expected_return_date', '<', now());
    }
}

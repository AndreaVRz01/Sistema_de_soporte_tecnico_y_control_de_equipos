<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LoanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'equipment_id',
        'condition_on_loan',
        'condition_on_return',
        'accessories_included',
        'notes_on_loan',
        'notes_on_return',
        'returned_at',
    ];

    protected function casts(): array
    {
        return [
            'returned_at' => 'datetime',
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class, 'loan_item_id');
    }

    public function paymentTicket(): HasOne
    {
        return $this->hasOne(PaymentTicket::class);
    }
}

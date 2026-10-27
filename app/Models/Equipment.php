<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    use HasFactory;

    protected $table = 'equipment';

    protected $fillable = [
        'category_id',
        'inventory_code',
        'serial_number',
        'name',
        'brand',
        'model',
        'specifications',
        'status',
        'physical_condition',
        'location',
        'acquisition_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'acquisition_date' => 'date',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function loanItems(): HasMany
    {
        return $this->hasMany(LoanItem::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    public function openIncidents(): HasMany
    {
        return $this->hasMany(Incident::class)->whereNotIn('status', ['resuelto', 'cancelado']);
    }

    public function isAvailable(): bool
    {
        return $this->status === 'disponible';
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', 'disponible');
    }

    public function scopeLoaned(Builder $query): Builder
    {
        return $query->where('status', 'prestado');
    }

    public function scopeInMaintenance(Builder $query): Builder
    {
        return $query->where('status', 'en_mantenimiento');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'event',
        'subject_type',
        'subject_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function displayName(): string
    {
        return match ($this->event) {
            'sesion.iniciada' => 'Inicio de sesión',
            'sesion.cerrada' => 'Cierre de sesión',
            'prestamo.solicitado' => 'Solicitud de préstamo enviada',
            'prestamo.registrado' => 'Préstamo registrado',
            'prestamo.aprobado' => 'Préstamo aprobado',
            'prestamo.rechazado' => 'Préstamo rechazado',
            'prestamo.entregado' => 'Activo entregado',
            'prestamo.devuelto' => 'Devolución registrada',
            'ticket.servicio_creado' => 'Reporte de soporte enviado',
            'usuario.personal_creado' => 'Personal operativo creado',
            'usuario.solicitante_registrado' => 'Solicitante registrado',
            'usuario.cuenta_creada' => 'Cuenta creada',
            'inventario.activo_creado' => 'Activo agregado al inventario',
            default => Str::headline(str_replace(['.', '_'], ' ', $this->event)),
        };
    }
}
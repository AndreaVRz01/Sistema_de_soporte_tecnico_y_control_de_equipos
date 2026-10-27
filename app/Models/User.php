<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Roles constantes
     */
    public const ROLE_ADMIN = 'admin';
    public const ROLE_SUPERVISOR = 'supervisor';
    public const ROLE_ENCARGADO_ACTIVOS = 'encargado_activos';
    public const ROLE_ENCARGADO_PRESTAMOS = 'encargado_prestamos';
    public const ROLE_TECNICO = 'tecnico';
    public const ROLE_USUARIO = 'usuario';

    /**
     * Tipos de usuarios institucionales (exclusivamente comunidad universitaria)
     */
    public const TYPE_ALUMNO = 'alumno';
    public const TYPE_DOCENTE = 'docente';
    public const TYPE_ADMINISTRATIVO = 'administrativo';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'user_type',
        'institutional_id',
        'career_or_department',
        'phone',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Helpers de rol
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isSupervisor(): bool
    {
        return $this->role === self::ROLE_SUPERVISOR;
    }

    public function isEncargadoActivos(): bool
    {
        return $this->role === self::ROLE_ENCARGADO_ACTIVOS;
    }

    public function isEncargadoPrestamos(): bool
    {
        return $this->role === self::ROLE_ENCARGADO_PRESTAMOS;
    }

    public function isTecnico(): bool
    {
        return $this->role === self::ROLE_TECNICO;
    }

    public function isUsuario(): bool
    {
        return $this->role === self::ROLE_USUARIO;
    }

    public function isDocente(): bool
    {
        return $this->user_type === self::TYPE_DOCENTE;
    }

    public function isAlumno(): bool
    {
        return $this->user_type === self::TYPE_ALUMNO;
    }

    public function canBorrow(): bool
    {
        return $this->status === 'activo' && !$this->hasActiveSanctions();
    }

    public function hasActiveSanctions(): bool
    {
        return $this->sanctions()->where('status', 'activa')->exists();
    }

    /**
     * Relaciones Eloquent
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class, 'user_id');
    }

    public function authorizedLoans(): HasMany
    {
        return $this->hasMany(Loan::class, 'authorized_by_id');
    }

    public function dispatchedLoans(): HasMany
    {
        return $this->hasMany(Loan::class, 'dispatched_by_id');
    }

    public function receivedLoans(): HasMany
    {
        return $this->hasMany(Loan::class, 'received_by_id');
    }

    public function reportedIncidents(): HasMany
    {
        return $this->hasMany(Incident::class, 'reported_by_id');
    }

    public function assignedIncidents(): HasMany
    {
        return $this->hasMany(Incident::class, 'assigned_technician_id');
    }

    public function technicianLogs(): HasMany
    {
        return $this->hasMany(IncidentLog::class, 'technician_id');
    }

    public function sanctions(): HasMany
    {
        return $this->hasMany(Sanction::class, 'user_id');
    }

    public function appliedSanctions(): HasMany
    {
        return $this->hasMany(Sanction::class, 'applied_by_id');
    }
}

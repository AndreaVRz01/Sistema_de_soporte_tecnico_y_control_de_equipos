<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EquipmentService
{
    private const STATUSES = [
        'disponible',
        'prestado',
        'mantenimiento',
        'en_mantenimiento',
        'en_reparacion',
        'baja',
        'reservado',
    ];

    private const FUNCTIONAL_STATUSES = [
        'funcional',
        'parcialmente_funcional',
        'sin_funcionar',
    ];

    public function __construct(private readonly ActivityLogService $activityLog)
    {
    }

    public function create(User $actor, array $data): Equipment
    {
        $this->authorize($actor);

        $validated = Validator::make($data, [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'inventory_code' => ['required', 'string', 'max:50', 'unique:equipment,inventory_code'],
            'serial_number' => ['required', 'string', 'max:80', 'unique:equipment,serial_number'],
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['required', 'string', 'max:60'],
            'model' => ['required', 'string', 'max:60'],
            'specifications' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(self::STATUSES)],
            'functional_status' => ['sometimes', Rule::in(self::FUNCTIONAL_STATUSES)],
            'physical_condition' => ['sometimes', Rule::in(['excelente', 'bueno', 'regular', 'malo'])],
            'location' => ['sometimes', 'string', 'max:100'],
            'acquisition_date' => ['nullable', 'date'],
            'warranty_until' => ['nullable', 'date'],
            'warranty_details' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ])->validate();

        return DB::transaction(function () use ($actor, $validated): Equipment {
            $equipment = Equipment::create($validated);
            $this->activityLog->record($actor, 'inventario.activo_creado', $equipment);

            return $equipment;
        });
    }

    public function update(User $actor, Equipment $equipment, array $data): Equipment
    {
        $this->authorize($actor);

        $validated = Validator::make($data, [
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'inventory_code' => ['sometimes', 'string', 'max:50', Rule::unique('equipment', 'inventory_code')->ignore($equipment->id)],
            'serial_number' => ['sometimes', 'string', 'max:80', Rule::unique('equipment', 'serial_number')->ignore($equipment->id)],
            'name' => ['sometimes', 'string', 'max:255'],
            'brand' => ['sometimes', 'string', 'max:60'],
            'model' => ['sometimes', 'string', 'max:60'],
            'specifications' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(self::STATUSES)],
            'functional_status' => ['sometimes', Rule::in(self::FUNCTIONAL_STATUSES)],
            'physical_condition' => ['sometimes', Rule::in(['excelente', 'bueno', 'regular', 'malo'])],
            'location' => ['sometimes', 'string', 'max:100'],
            'acquisition_date' => ['nullable', 'date'],
            'warranty_until' => ['nullable', 'date'],
            'warranty_details' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ])->validate();

        return DB::transaction(function () use ($actor, $equipment, $validated): Equipment {
            $equipment->update($validated);
            $this->activityLog->record($actor, 'inventario.activo_actualizado', $equipment, ['fields' => array_keys($validated)]);

            return $equipment->refresh();
        });
    }

    public function setStatus(User $actor, Equipment $equipment, string $status, string $functionalStatus): Equipment
    {
        return $this->update($actor, $equipment, [
            'status' => $status,
            'functional_status' => $functionalStatus,
        ]);
    }

    private function authorize(User $actor): void
    {
        if (!\in_array($actor->role, [User::ROLE_ADMIN, User::ROLE_ENCARGADO_ACTIVOS], true)) {
            throw new AuthorizationException('Solo el administrador o el encargado de activos puede modificar el inventario.');
        }
    }
}
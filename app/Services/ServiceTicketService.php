<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\IncidentComment;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ServiceTicketService
{
    private const STATUSES = [
        'reportado',
        'asignado',
        'en_diagnostico',
        'en_reparacion',
        'resuelto',
        'baja_definitiva',
        'cancelado',
    ];

    public function __construct(private readonly ActivityLogService $activityLog)
    {
    }

    public function createTicket(User $reporter, array $data): Incident
    {
        if (!\in_array($reporter->role, [
            User::ROLE_ADMIN,
            User::ROLE_SUPERVISOR,
            User::ROLE_ENCARGADO_ACTIVOS,
            User::ROLE_TECNICO,
        ], true)) {
            throw new AuthorizationException('Solo personal técnico o de activos puede crear tickets de servicio.');
        }

        $validated = Validator::make($data, [
            'equipment_id' => ['required', 'integer', 'exists:equipment,id'],
            'loan_item_id' => ['nullable', 'integer', 'exists:loan_items,id'],
            'assigned_technician_id' => ['nullable', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:10000'],
            'type' => ['required', Rule::in([
                'hardware',
                'software',
                'daño_fisico',
                'desgaste_natural',
                'perdida_accesorio',
                'mantenimiento_preventivo',
            ])],
            'priority' => ['sometimes', Rule::in(['baja', 'media', 'alta', 'critica'])],
        ])->validate();

        if (isset($validated['assigned_technician_id'])) {
            $technician = User::findOrFail($validated['assigned_technician_id']);
            if (!$technician->isTecnico()) {
                throw ValidationException::withMessages([
                    'assigned_technician_id' => 'El usuario asignado debe tener el rol de técnico.',
                ]);
            }
        }

        return DB::transaction(function () use ($reporter, $validated): Incident {
            $ticket = Incident::create($validated + [
                'ticket_code' => 'INC-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
                'reported_by_id' => $reporter->id,
                'status' => isset($validated['assigned_technician_id']) ? 'asignado' : 'reportado',
                'reported_at' => now(),
            ]);

            $equipment = \App\Models\Equipment::query()->lockForUpdate()->findOrFail($ticket->equipment_id);
            if ($equipment->status === 'baja') {
                throw ValidationException::withMessages([
                    'equipment_id' => 'No se puede abrir un ticket operativo para un activo dado de baja.',
                ]);
            }

            $this->activityLog->record($reporter, 'ticket.servicio_creado', $ticket);

            return $ticket;
        });
    }

    public function assignTechnician(User $actor, Incident $ticket, User $technician): Incident
    {
        if (!\in_array($actor->role, [User::ROLE_ADMIN, User::ROLE_SUPERVISOR, User::ROLE_ENCARGADO_ACTIVOS], true)) {
            throw new AuthorizationException('No tienes permiso para asignar tickets.');
        }
        if (!$technician->isTecnico()) {
            throw ValidationException::withMessages([
                'technician' => 'El usuario asignado debe tener el rol de técnico.',
            ]);
        }

        return DB::transaction(function () use ($actor, $ticket, $technician): Incident {
            $ticket = Incident::query()->lockForUpdate()->findOrFail($ticket->id);
            if (\in_array($ticket->status, ['resuelto', 'baja_definitiva', 'cancelado'], true)) {
                throw ValidationException::withMessages(['ticket' => 'No se puede asignar un ticket cerrado.']);
            }

            $ticket->update([
                'assigned_technician_id' => $technician->id,
                'status' => 'asignado',
            ]);
            $this->activityLog->record($actor, 'ticket.tecnico_asignado', $ticket, ['technician_id' => $technician->id]);

            return $ticket->refresh();
        });
    }

    public function addComment(User $actor, Incident $ticket, string $comment): IncidentComment
    {
        $this->authorizeCommenter($actor, $ticket);

        $validated = Validator::make(['comment' => $comment], [
            'comment' => ['required', 'string', 'max:5000'],
        ])->validate();

        return DB::transaction(function () use ($actor, $ticket, $validated): IncidentComment {
            $comment = $ticket->comments()->create([
                'user_id' => $actor->id,
                'comment' => $validated['comment'],
            ]);
            $this->activityLog->record($actor, 'ticket.comentario_agregado', $comment);

            return $comment;
        });
    }

    public function updateStatus(User $technician, Incident $ticket, array $data): Incident
    {
        if (!$technician->isTecnico() || $ticket->assigned_technician_id !== $technician->id) {
            throw new AuthorizationException('Solo el técnico asignado puede actualizar este ticket.');
        }

        $validated = Validator::make($data, [
            'status' => ['required', Rule::in(self::STATUSES)],
            'diagnostic' => ['nullable', 'string'],
            'solution_applied' => ['nullable', 'string'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'actual_cost' => ['nullable', 'numeric', 'min:0'],
            'functional_status' => ['nullable', Rule::in(['funcional', 'parcialmente_funcional', 'sin_funcionar'])],
            'notes' => ['nullable', 'string'],
        ])->validate();

        return DB::transaction(function () use ($technician, $ticket, $validated): Incident {
            $ticket = Incident::query()->lockForUpdate()->findOrFail($ticket->id);
            if ($ticket->assigned_technician_id !== $technician->id) {
                throw new AuthorizationException('El ticket ya no está asignado a este técnico.');
            }
            if (\in_array($ticket->status, ['resuelto', 'baja_definitiva', 'cancelado'], true)) {
                throw ValidationException::withMessages(['ticket' => 'No se puede actualizar un ticket cerrado.']);
            }

            $previousStatus = $ticket->status;
            $ticket->update([
                'status' => $validated['status'],
                'diagnostic' => $validated['diagnostic'] ?? $ticket->diagnostic,
                'solution_applied' => $validated['solution_applied'] ?? $ticket->solution_applied,
                'estimated_cost' => $validated['estimated_cost'] ?? $ticket->estimated_cost,
                'actual_cost' => $validated['actual_cost'] ?? $ticket->actual_cost,
                'resolved_at' => \in_array($validated['status'], ['resuelto', 'baja_definitiva'], true) ? now() : null,
            ]);
            $ticket->logs()->create([
                'technician_id' => $technician->id,
                'activity' => 'Estado actualizado',
                'notes' => $validated['notes'] ?? null,
                'status_before' => $previousStatus,
                'status_after' => $validated['status'],
            ]);

            $equipment = \App\Models\Equipment::query()->lockForUpdate()->findOrFail($ticket->equipment_id);
            if ($validated['status'] === 'baja_definitiva') {
                $equipment->update(['status' => 'baja', 'functional_status' => 'sin_funcionar']);
            } elseif ($validated['status'] === 'resuelto') {
                $functionalStatus = $validated['functional_status'] ?? 'funcional';
                $equipment->update([
                    'functional_status' => $functionalStatus,
                    'status' => $functionalStatus === 'sin_funcionar' ? 'baja' : 'disponible',
                ]);
            } elseif ($validated['status'] === 'en_reparacion') {
                $equipment->update(['status' => 'en_reparacion']);
            } elseif ($validated['status'] === 'en_diagnostico') {
                $equipment->update(['status' => 'mantenimiento']);
            }

            $this->activityLog->record($technician, 'ticket.estado_actualizado', $ticket, [
                'from' => $previousStatus,
                'to' => $ticket->status,
            ]);

            return $ticket->refresh();
        });
    }

    private function authorizeCommenter(User $actor, Incident $ticket): void
    {
        $relatedUser = $ticket->reported_by_id === $actor->id
            || $ticket->assigned_technician_id === $actor->id;
        $manager = \in_array($actor->role, [
            User::ROLE_ADMIN,
            User::ROLE_SUPERVISOR,
            User::ROLE_ENCARGADO_ACTIVOS,
            User::ROLE_ENCARGADO_PRESTAMOS,
        ], true);

        if (!$relatedUser && !$manager) {
            throw new AuthorizationException('No tienes permiso para comentar en este ticket.');
        }
    }
}
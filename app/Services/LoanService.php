<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\Incident;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\PaymentTicket;
use App\Models\Sanction;
use App\Models\User;
use App\Notifications\LoanApprovalRequired;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LoanService
{
    public function __construct(
        private readonly ActivityLogService $activityLog,
        private readonly PaymentTicketService $paymentTickets
    ) {
    }

    public function requestLoan(
        User $requester,
        array $equipmentIds,
        string $purpose,
        string $expectedReturnDate,
        ?string $locationOfUse = null
    ): Loan {
        if (!$requester->isUsuario()) {
            throw new AuthorizationException('Solo los usuarios solicitantes pueden pedir préstamos.');
        }
        if (!$requester->canBorrow()) {
            throw ValidationException::withMessages([
                'user' => 'El usuario está inactivo, bloqueado o tiene más de cuatro penalizaciones.',
            ]);
        }

        $validated = Validator::make([
            'equipment_ids' => $equipmentIds,
            'purpose' => $purpose,
            'expected_return_date' => $expectedReturnDate,
            'location_of_use' => $locationOfUse,
        ], [
            'equipment_ids' => ['required', 'array', 'min:1'],
            'equipment_ids.*' => ['required', 'integer', 'distinct', 'exists:equipment,id'],
            'purpose' => ['required', 'string', 'max:5000'],
            'expected_return_date' => ['required', 'date', 'after:now'],
            'location_of_use' => ['nullable', 'string', 'max:120'],
        ])->validate();

        return DB::transaction(function () use ($requester, $validated): Loan {
            $equipment = Equipment::query()
                ->whereIn('id', $validated['equipment_ids'])
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($equipment->count() !== count($validated['equipment_ids'])) {
                throw ValidationException::withMessages(['equipment_ids' => 'Uno o más activos no existen.']);
            }

            foreach ($equipment as $asset) {
                if ($asset->status !== 'disponible' || $asset->functional_status === 'sin_funcionar') {
                    throw ValidationException::withMessages([
                        'equipment_ids' => "El activo {$asset->inventory_code} no está disponible para préstamo.",
                    ]);
                }
            }

            $requiresSupervisor = count($validated['equipment_ids']) > 10;
            $loan = Loan::create([
                'folio' => 'PRE-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
                'user_id' => $requester->id,
                'purpose' => $validated['purpose'],
                'location_of_use' => $validated['location_of_use'] ?? null,
                'expected_return_date' => $validated['expected_return_date'],
                'status' => 'solicitado',
                'requires_supervisor_approval' => $requiresSupervisor,
            ]);

            foreach ($equipment as $asset) {
                $condition = match ($asset->physical_condition) {
                    'excelente' => 'excelente',
                    'regular' => 'regular',
                    default => 'bueno',
                };
                $loan->items()->create([
                    'equipment_id' => $asset->id,
                    'condition_on_loan' => $condition,
                ]);
                $asset->update(['status' => 'reservado']);
            }

            $this->activityLog->record($requester, 'prestamo.solicitado', $loan, [
                'equipment_count' => $equipment->count(),
                'requires_supervisor_approval' => $requiresSupervisor,
            ]);

            if ($requiresSupervisor) {
                User::query()->where('role', User::ROLE_SUPERVISOR)->get()
                    ->each(fn (User $supervisor) => $supervisor->notify(new LoanApprovalRequired($loan)));
            }

            return $loan->load('items.equipment.category');
        });
    }

    public function approve(User $approver, Loan $loan): Loan
    {
        return DB::transaction(function () use ($approver, $loan): Loan {
            $loan = Loan::query()->lockForUpdate()->findOrFail($loan->id);
            $this->authorizeApproval($approver, $loan);

            if ($loan->status !== 'solicitado') {
                throw ValidationException::withMessages(['loan' => 'La solicitud ya fue procesada.']);
            }

            if ($loan->requires_supervisor_approval) {
                $loan->supervisor_approved_by_id = $approver->id;
                $loan->supervisor_approved_at = now();
            }
            $loan->authorized_by_id = $approver->id;
            $loan->status = 'aprobado';
            $loan->save();

            $this->activityLog->record($approver, 'prestamo.aprobado', $loan, [
                'supervisor_approval' => $loan->requires_supervisor_approval,
            ]);

            return $loan->refresh();
        });
    }

    public function reject(User $approver, Loan $loan, string $reason): Loan
    {
        return DB::transaction(function () use ($approver, $loan, $reason): Loan {
            $loan = Loan::query()->lockForUpdate()->findOrFail($loan->id);
            $this->authorizeApproval($approver, $loan);
            if ($loan->status !== 'solicitado') {
                throw ValidationException::withMessages(['loan' => 'La solicitud ya fue procesada.']);
            }

            $validated = Validator::make(['reason' => $reason], [
                'reason' => ['required', 'string', 'max:5000'],
            ])->validate();

            $loan->update([
                'status' => 'rechazado',
                'authorized_by_id' => $approver->id,
                'rejection_reason' => $validated['reason'],
            ]);
            $this->releaseReservedEquipment($loan);
            $this->activityLog->record($approver, 'prestamo.rechazado', $loan);

            return $loan->refresh();
        });
    }

    public function dispatch(User $dispatcher, Loan $loan): Loan
    {
        $this->authorizeLoanManager($dispatcher);

        return DB::transaction(function () use ($dispatcher, $loan): Loan {
            $loan = Loan::query()->with('items')->lockForUpdate()->findOrFail($loan->id);
            if ($loan->status !== 'aprobado') {
                throw ValidationException::withMessages(['loan' => 'Solo se pueden entregar préstamos aprobados.']);
            }

            foreach ($loan->items as $item) {
                $asset = Equipment::query()->lockForUpdate()->findOrFail($item->equipment_id);
                if ($asset->status !== 'reservado') {
                    throw ValidationException::withMessages(['loan' => 'Uno de los activos ya no está reservado.']);
                }
                $asset->update(['status' => 'prestado']);
            }

            $loan->update([
                'status' => 'entregado',
                'dispatched_by_id' => $dispatcher->id,
                'loan_date' => now(),
            ]);
            $this->activityLog->record($dispatcher, 'prestamo.entregado', $loan);

            return $loan->refresh();
        });
    }

    public function registerReturn(User $receiver, Loan $loan, array $returnedItems): Loan
    {
        $this->authorizeLoanManager($receiver);

        $validated = Validator::make(['items' => $returnedItems], [
            'items' => ['required', 'array', 'min:1'],
            'items.*.equipment_id' => ['required', 'integer', 'distinct'],
            'items.*.condition_on_return' => ['required', Rule::in(['excelente', 'bueno', 'regular', 'dañado', 'incompleto'])],
            'items.*.notes_on_return' => ['nullable', 'string', 'max:5000'],
            'items.*.amount_to_pay' => ['nullable', 'numeric', 'min:0'],
        ])->validate();

        return DB::transaction(function () use ($receiver, $loan, $validated): Loan {
            $loan = Loan::query()->lockForUpdate()->findOrFail($loan->id);
            if (!\in_array($loan->status, ['entregado', 'vencido'], true)) {
                throw ValidationException::withMessages(['loan' => 'El préstamo no está pendiente de devolución.']);
            }

            $returnData = collect($validated['items'])->keyBy('equipment_id');
            $loanItems = LoanItem::query()
                ->where('loan_id', $loan->id)
                ->whereIn('equipment_id', $returnData->keys())
                ->with('equipment')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($loanItems->count() !== $returnData->count()) {
                throw ValidationException::withMessages(['items' => 'Hay equipos que no pertenecen a este préstamo.']);
            }

            foreach ($loanItems as $item) {
                if ($item->returned_at) {
                    throw ValidationException::withMessages([
                        'items' => "El activo {$item->equipment->inventory_code} ya fue devuelto.",
                    ]);
                }

                $data = $returnData->get($item->equipment_id);
                $condition = $data['condition_on_return'];
                $damaged = \in_array($condition, ['dañado', 'incompleto'], true);
                $item->update([
                    'condition_on_return' => $condition,
                    'notes_on_return' => $data['notes_on_return'] ?? null,
                    'returned_at' => now(),
                ]);

                $item->equipment->update($damaged ? [
                    'status' => 'en_reparacion',
                    'functional_status' => 'parcialmente_funcional',
                    'physical_condition' => 'malo',
                ] : ['status' => 'disponible']);

                if ($damaged) {
                    $ticket = Incident::create([
                        'ticket_code' => 'INC-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
                        'equipment_id' => $item->equipment_id,
                        'loan_item_id' => $item->id,
                        'reported_by_id' => $receiver->id,
                        'title' => 'Revisión por devolución con daño',
                        'description' => $data['notes_on_return'] ?? "Equipo devuelto con condición: {$condition}.",
                        'type' => $condition === 'incompleto' ? 'perdida_accesorio' : 'daño_fisico',
                        'priority' => 'alta',
                        'status' => 'reportado',
                        'reported_at' => now(),
                    ]);
                    $this->applySanction(
                        $receiver,
                        $loan,
                        'cobro_reparacion',
                        "Equipo devuelto con condición: {$condition}.",
                        $data['amount_to_pay'] ?? null,
                        $item->id
                    );
                    $this->activityLog->record($receiver, 'ticket.dano_devolucion_creado', $ticket);
                }
            }

            $overdue = $loan->expected_return_date->isPast();
            if ($overdue) {
                $this->applySanction($receiver, $loan, 'suspension_temporal', 'Devolución posterior a la fecha límite.');
            }

            $allReturned = !$loan->items()->whereNull('returned_at')->exists();
            $hasObservations = $loan->items()->whereIn('condition_on_return', ['dañado', 'incompleto'])->exists();
            $loan->update([
                'status' => $allReturned
                    ? ($hasObservations ? 'devuelto_con_observacion' : 'devuelto')
                    : ($overdue ? 'vencido' : 'entregado'),
                'received_by_id' => $receiver->id,
                'actual_return_date' => $allReturned ? now() : null,
            ]);
            $this->activityLog->record($receiver, 'prestamo.devolucion_registrada', $loan, [
                'returned_count' => $loanItems->count(),
                'all_returned' => $allReturned,
            ]);

            return $loan->refresh();
        });
    }

    public function markOverdueLoans(?User $actor = null): int
    {
        if ($actor) {
            $this->authorizeLoanManager($actor);
        }
        $loanIds = Loan::query()
            ->where('status', 'entregado')
            ->where('expected_return_date', '<', now())
            ->pluck('id');

        $markedCount = 0;
        foreach ($loanIds as $loanId) {
            DB::transaction(function () use ($actor, $loanId, &$markedCount): void {
                $loan = Loan::query()->lockForUpdate()->findOrFail($loanId);
                if ($loan->status !== 'entregado' || !$loan->expected_return_date->isPast()) {
                    return;
                }

                $loan->update(['status' => 'vencido']);
                $this->applySanction($actor, $loan, 'suspension_temporal', 'Préstamo vencido sin devolución registrada.');
                $this->activityLog->record($actor, 'prestamo.vencido', $loan);
                $markedCount++;
            });
        }

        return $markedCount;
    }

    public function reportNonReturn(
        User $actor,
        Loan $loan,
        int $equipmentId,
        float $amountDue,
        string $reason
    ): PaymentTicket {
        return $this->paymentTickets->reportNonReturn($actor, $loan, $equipmentId, $amountDue, $reason);
    }

    private function applySanction(
        ?User $actor,
        Loan $loan,
        string $type,
        string $reason,
        ?float $amount = null,
        ?int $loanItemId = null
    ): ?Sanction {
        $exists = Sanction::query()
            ->where('loan_id', $loan->id)
            ->where('sanction_type', $type)
            ->when($loanItemId, fn ($query) => $query->where('loan_item_id', $loanItemId))
            ->exists();

        if ($exists) {
            return null;
        }

        return Sanction::create([
            'user_id' => $loan->user_id,
            'loan_id' => $loan->id,
            'loan_item_id' => $loanItemId,
            'applied_by_id' => $actor?->id,
            'is_automatic' => $actor === null,
            'sanction_type' => $type,
            'reason' => $reason,
            'start_date' => today(),
            'end_date' => $type === 'suspension_temporal' ? today()->addDays(7) : null,
            'amount_to_pay' => $amount,
            'status' => 'activa',
        ]);
    }

    private function releaseReservedEquipment(Loan $loan): void
    {
        foreach ($loan->items as $item) {
            Equipment::query()
                ->whereKey($item->equipment_id)
                ->where('status', 'reservado')
                ->update(['status' => 'disponible']);
        }
    }

    private function authorizeApproval(User $actor, Loan $loan): void
    {
        if ($loan->requires_supervisor_approval) {
            if (!$actor->isSupervisor()) {
                throw new AuthorizationException('Los préstamos de más de 10 equipos requieren aprobación del supervisor.');
            }

            return;
        }

        if (!\in_array($actor->role, [
            User::ROLE_ADMIN,
            User::ROLE_SUPERVISOR,
            User::ROLE_ENCARGADO_PRESTAMOS,
        ], true)) {
            throw new AuthorizationException('No tienes permiso para aprobar préstamos.');
        }
    }

    private function authorizeLoanManager(User $actor): void
    {
        if (!\in_array($actor->role, [User::ROLE_ADMIN, User::ROLE_ENCARGADO_PRESTAMOS], true)) {
            throw new AuthorizationException('Solo el administrador o el encargado de préstamos puede realizar esta operación.');
        }
    }
}
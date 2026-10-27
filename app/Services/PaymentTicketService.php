<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\PaymentTicket;
use App\Models\Sanction;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentTicketService
{
    public function __construct(private readonly ActivityLogService $activityLog)
    {
    }

    public function reportNonReturn(
        User $actor,
        Loan $loan,
        int $equipmentId,
        float $amountDue,
        string $reason
    ): PaymentTicket {
        $this->authorize($actor);

        $validated = Validator::make([
            'amount_due' => $amountDue,
            'reason' => $reason,
        ], [
            'amount_due' => ['required', 'numeric', 'min:0'],
            'reason' => ['required', 'string', 'max:5000'],
        ])->validate();

        return DB::transaction(function () use ($actor, $loan, $equipmentId, $validated): PaymentTicket {
            $lockedLoan = Loan::query()->lockForUpdate()->findOrFail($loan->id);
            if (!\in_array($lockedLoan->status, ['entregado', 'vencido'], true)
                || $lockedLoan->expected_return_date->isFuture()) {
                throw ValidationException::withMessages([
                    'loan' => 'Solo se puede reportar como no devuelto un equipo cuyo préstamo ya venció.',
                ]);
            }

            $loanItem = LoanItem::query()
                ->where('loan_id', $lockedLoan->id)
                ->where('equipment_id', $equipmentId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($loanItem->returned_at) {
                throw ValidationException::withMessages([
                    'equipment_id' => 'El equipo ya fue registrado como devuelto.',
                ]);
            }

            $ticket = PaymentTicket::firstOrCreate(
                ['loan_item_id' => $loanItem->id],
                [
                    'ticket_code' => 'PAG-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
                    'loan_id' => $lockedLoan->id,
                    'user_id' => $lockedLoan->user_id,
                    'equipment_id' => $equipmentId,
                    'created_by_id' => $actor->id,
                    'reason' => $validated['reason'],
                    'amount_due' => $validated['amount_due'],
                    'status' => 'pendiente',
                ]
            );

            $lockedLoan->update(['status' => 'vencido']);
            Equipment::query()->whereKey($equipmentId)->update([
                'status' => 'baja',
                'functional_status' => 'sin_funcionar',
            ]);

            $hasPermanentSanction = Sanction::query()
                ->where('user_id', $lockedLoan->user_id)
                ->where('loan_id', $lockedLoan->id)
                ->where('sanction_type', 'bloqueo_definitivo')
                ->exists();

            if (!$hasPermanentSanction) {
                Sanction::create([
                    'user_id' => $lockedLoan->user_id,
                    'loan_id' => $lockedLoan->id,
                    'applied_by_id' => $actor->id,
                    'sanction_type' => 'bloqueo_definitivo',
                    'reason' => $validated['reason'],
                    'start_date' => today(),
                    'status' => 'activa',
                ]);
            }

            $this->activityLog->record($actor, 'prestamo.no_devolucion_reportada', $ticket, [
                'loan_id' => $lockedLoan->id,
                'equipment_id' => $equipmentId,
                'amount_due' => $validated['amount_due'],
            ]);

            return $ticket;
        });
    }

    public function markPaid(User $actor, PaymentTicket $ticket, ?string $notes = null): PaymentTicket
    {
        $this->authorize($actor);

        return DB::transaction(function () use ($actor, $ticket, $notes): PaymentTicket {
            $ticket = PaymentTicket::query()->lockForUpdate()->findOrFail($ticket->id);
            if ($ticket->status !== 'pendiente') {
                throw ValidationException::withMessages([
                    'payment_ticket' => 'El ticket ya no está pendiente de pago.',
                ]);
            }

            $ticket->update([
                'status' => 'pagado',
                'paid_by_id' => $actor->id,
                'paid_at' => now(),
                'notes' => $notes,
            ]);

            if (!$ticket->loan->paymentTickets()->where('status', 'pendiente')->exists()) {
                Sanction::query()
                    ->where('loan_id', $ticket->loan_id)
                    ->where('sanction_type', 'bloqueo_definitivo')
                    ->update(['is_paid' => true]);
            }

            $this->activityLog->record($actor, 'pago.ticket_pagado', $ticket);

            return $ticket->refresh();
        });
    }

    private function authorize(User $actor): void
    {
        if (!\in_array($actor->role, [User::ROLE_ADMIN, User::ROLE_ENCARGADO_PRESTAMOS], true)) {
            throw new AuthorizationException('Solo el administrador o el encargado de préstamos puede gestionar tickets de pago.');
        }
    }
}
<?php

namespace App\Notifications;

use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LoanRejected extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Loan $loan,
        private readonly ?string $rejectedByName = null
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $reason = $this->loan->rejection_reason ?: 'No especificado.';

        return [
            'loan_id' => $this->loan->id,
            'folio' => $this->loan->folio,
            'rejection_reason' => $reason,
            'rejected_by' => $this->rejectedByName ?? $this->loan->authorizedBy?->name,
            'message' => "Tu solicitud de préstamo {$this->loan->folio} no fue aceptada. Motivo: {$reason}",
        ];
    }
}

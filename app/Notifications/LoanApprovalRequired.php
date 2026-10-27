<?php

namespace App\Notifications;

use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LoanApprovalRequired extends Notification
{
    use Queueable;

    public function __construct(private readonly Loan $loan)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'loan_id' => $this->loan->id,
            'folio' => $this->loan->folio,
            'requester' => $this->loan->user?->name,
            'equipment_count' => $this->loan->items()->count(),
            'message' => 'Un préstamo de más de 10 equipos requiere revisión y aprobación.',
        ];
    }
}
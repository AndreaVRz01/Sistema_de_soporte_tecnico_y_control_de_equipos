<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Equipment;
use App\Services\AuditService;
use App\Services\ServiceTicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserPortalController extends Controller
{
    public function activity(Request $request): View
    {
        $activityLogs = ActivityLog::with('subject')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return view('private.user-activity', compact('activityLogs'));
    }

    public function createReport(): View
    {
        $equipment = Equipment::where('status', '!=', 'baja')->orderBy('name')->get();

        return view('private.reports.create', compact('equipment'));
    }

    public function storeReport(Request $request, ServiceTicketService $tickets): RedirectResponse
    {
        $validated = $request->validate([
            'equipment_id' => ['required', 'exists:equipment,id'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:10000'],
            'type' => ['required', 'in:hardware,software,daño_fisico,desgaste_natural,perdida_accesorio,mantenimiento_preventivo'],
        ]);

        $user = $request->user();
        $ticket = $tickets->createTicket($user, $validated);

        AuditService::log(
            'Incident',
            $ticket->id,
            'REPORTE_USUARIO',
            "Reporte {$ticket->ticket_code} enviado por {$user->name}: {$ticket->title}",
            ['equipment_id' => $ticket->equipment_id, 'type' => $ticket->type],
            'normal',
            $user->id
        );

        return redirect()->route('portal.activity')
            ->with('success', "Tu reporte {$ticket->ticket_code} quedó registrado.");
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Sanction;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SanctionController extends Controller
{
    /**
     * Muestra el panel de gestión de sanciones disciplinarias y cobros por avería.
     */
    public function index(Request $request): View
    {
        $query = Sanction::with(['user', 'appliedBy', 'loan', 'incident']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->input('sanction_type')) {
            $query->where('sanction_type', $type);
        }

        if ($search = $request->input('search')) {
            $query->whereHas('user', function ($uq) use ($search) {
                $uq->where('name', 'like', "%{$search}%")
                   ->orWhere('institutional_id', 'like', "%{$search}%");
            });
        }

        $counts = [
            'total' => Sanction::count(),
            'activas' => Sanction::where('status', 'activa')->count(),
            'cumplidas' => Sanction::where('status', 'cumplida')->count(),
        ];

        $sanctions = $query->latest('start_date')->paginate(10)->withQueryString();
        $users = User::where('status', '!=', 'inactivo')->orderBy('name')->get();

        return view('private.sanctions.index', compact('sanctions', 'counts', 'users'));
    }

    /**
     * Aplica una sanción institucional o suspensión preventiva a un usuario.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'sanction_type' => ['required', 'in:suspension_temporal,cobro_reparacion,amonestacion_escrita,bloqueo_definitivo'],
            'reason' => ['required', 'string', 'max:500'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'amount_to_pay' => ['nullable', 'numeric', 'min:0'],
            'loan_id' => ['nullable', 'exists:loans,id'],
            'incident_id' => ['nullable', 'exists:incidents,id'],
        ]);

        DB::beginTransaction();
        try {
            $validated['applied_by_id'] = Auth::id();
            $validated['status'] = 'activa';
            $validated['is_paid'] = false;

            $sanction = Sanction::create($validated);

            // Cambiar estatus del usuario a sancionado
            $user = User::findOrFail($validated['user_id']);
            $user->update(['status' => 'sancionado']);

            DB::commit();

            AuditService::log(
                'Sanction',
                $sanction->id,
                'SANCION_APLICADA',
                "Sanción impuesta a {$user->name} ({$sanction->sanction_type}). Motivo: {$sanction->reason}",
                $validated,
                'advertencia'
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sanción aplicada exitosamente.',
                    'sanction' => $sanction,
                ]);
            }

            return redirect()->route('sanctions.index')->with('success', "Sanción aplicada a {$user->name}. Se inhabilitaron sus solicitudes de préstamo.");
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withInput()->with('error', 'Error al aplicar sanción: ' . $e->getMessage());
        }
    }

    /**
     * Dictamina el cumplimiento, pago o levantamiento de una sanción.
     */
    public function resolve(Request $request, Sanction $sanction): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:cumplida,cancelada'],
            'resolution_notes' => ['required', 'string', 'max:500'],
            'is_paid' => ['nullable', 'boolean'],
        ]);

        DB::beginTransaction();
        try {
            $sanction->update([
                'status' => $validated['status'],
                'resolution_notes' => $validated['resolution_notes'],
                'is_paid' => $request->boolean('is_paid'),
            ]);

            $user = $sanction->user;

            // Si ya no tiene sanciones activas pendientes, restaurar a 'activo'
            $hasOtherActive = Sanction::where('user_id', $user->id)
                ->where('status', 'activa')
                ->where('id', '!=', $sanction->id)
                ->exists();

            if (!$hasOtherActive && $user->status === 'sancionado') {
                $user->update(['status' => 'activo']);
            }

            DB::commit();

            AuditService::log(
                'Sanction',
                $sanction->id,
                'SANCION_LEVANTADA',
                "Sanción levantada para {$user->name}. Estatus: {$validated['status']}",
                $validated,
                'normal'
            );

            return redirect()->route('sanctions.index')->with('success', "Sanción actualizada con éxito.");
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->with('error', 'Error al resolver la sanción: ' . $e->getMessage());
        }
    }
}


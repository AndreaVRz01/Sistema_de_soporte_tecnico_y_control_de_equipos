<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditService;
use App\Services\PasswordRecoveryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Muestra la interfaz de inicio de sesión y recuperación con token.
     * Basado en: docs/stitch_asset_loan_maintenance_system/inicio_de_sesi_n_y_recuperaci_n_con_token
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->to($this->destinationFor(Auth::user()));
        }

        return view('auth.login');
    }

    /**
     * Procesa la autenticación del operador o usuario institucional.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'El correo institucional o número de empleado/matrícula es requerido.',
            'password.required' => 'La contraseña de acceso es requerida.',
        ]);

        $loginInput = trim($credentials['login']);
        $field = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'institutional_id';

        $user = User::where($field, $loginInput)->first();

        if (!$user) {
            AuditService::log('Auth', null, 'LOGIN_FAILED', "Intento fallido con identificador no existente: {$loginInput}", null, 'advertencia');
            return back()->withInput()->with('error', 'Las credenciales proporcionadas no corresponden a un operador o usuario registrado.');
        }

        if ($user->status === 'inactivo') {
            AuditService::log('Auth', $user->id, 'LOGIN_BLOCKED', "Intento de acceso de cuenta inactiva: {$user->email}", null, 'advertencia', $user->id);
            return back()->withInput()->with('error', 'Su cuenta institucional está temporalmente inactiva. Solicite su reactivación al Administrador.');
        }

        $remember = $request->boolean('remember');

        if (Auth::attempt([$field => $loginInput, 'password' => $credentials['password']], $remember)) {
            $request->session()->regenerate();

            app(\App\Services\ActivityLogService::class)->record($user, 'sesion.iniciada', $user);
            AuditService::log('Auth', $user->id, 'LOGIN', "Inicio de sesión exitoso ({$user->role}) desde {$request->ip()}", null, 'normal', $user->id);

            return redirect()->to($this->destinationFor($user))
                ->with('success', "Bienvenido(a), {$user->name}. Acceso autorizado para {$user->role}.");
        }

        AuditService::log('Auth', $user->id, 'LOGIN_FAILED', "Contraseña incorrecta para el usuario: {$user->email}", null, 'advertencia', $user->id);

        return back()->withInput()->with('error', 'Contraseña incorrecta. Verifique sus credenciales o use la recuperación por token.');
    }

    /**
    * Envía por correo un enlace temporal para restablecer la contraseña.
     */
    public function sendRecoveryToken(Request $request, PasswordRecoveryService $passwordRecovery): RedirectResponse
    {
        $request->validate([
            'recovery_identifier' => ['required', 'string'],
        ], [
            'recovery_identifier.required' => 'Ingrese su correo institucional o número de empleado/matrícula.',
        ]);

        $input = trim($request->input('recovery_identifier'));
        $field = filter_var($input, FILTER_VALIDATE_EMAIL) ? 'email' : 'institutional_id';

        $user = User::where($field, $input)->first();

        if ($user) {
            $status = $passwordRecovery->sendResetLink($user->email);

            if ($status === Password::RESET_LINK_SENT) {
                AuditService::log(
                    'Auth',
                    $user->id,
                    'PASSWORD_RESET_LINK_SENT',
                    "Enlace de recuperación enviado a {$user->email}",
                    null,
                    'normal',
                    $user->id
                );
            }
        }

        return back()->with('token_sent', true)
            ->with('success', 'Si los datos corresponden a una cuenta registrada, recibirás un correo con el enlace para cambiar tu contraseña.');
    }

    public function showResetForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request, PasswordRecoveryService $passwordRecovery): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'token.required' => 'El token de autorización es requerido.',
            'password.min' => 'La nueva clave debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        $status = $passwordRecovery->resetPassword($request->only('email', 'token', 'password', 'password_confirmation'));

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withInput($request->only('email'))
                ->with('error', __($status));
        }

        $user = User::where('email', $request->input('email'))->first();

        if ($user) {
            app(\App\Services\ActivityLogService::class)->record($user, 'contrasena.restablecida', $user);
            AuditService::log('Auth', $user->id, 'PASSWORD_RESET', "Contraseña restablecida para {$user->email}", null, 'normal', $user->id);
        }

        return redirect()->route('login')->with('success', 'Tu contraseña fue actualizada. Inicia sesión con la nueva contraseña.');
    }

    /**
     * Cierra la sesión activa del operador.
     */
    public function logout(Request $request): RedirectResponse
    {
        $userId = Auth::id();
        $userEmail = Auth::user()?->email;
        $user = Auth::user();

        if ($user) {
            app(\App\Services\ActivityLogService::class)->record($user, 'sesion.cerrada', $user);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($userId) {
            AuditService::log('Auth', $userId, 'LOGOUT', "Cierre de sesión ordenado por {$userEmail}", null, 'normal', $userId);
        }

        return redirect()->route('login')->with('success', 'Sesión cerrada de forma segura.');
    }

    private function destinationFor(User $user): string
    {
        return match ($user->role) {
            User::ROLE_USUARIO => route('dashboard'),
            User::ROLE_ENCARGADO_ACTIVOS => route('equipment.index'),
            User::ROLE_ENCARGADO_PRESTAMOS => route('loans.index'),
            User::ROLE_TECNICO => route('incidents.index'),
            default => route('dashboard'),
        };
    }
}


<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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

            AuditService::log('Auth', $user->id, 'LOGIN', "Inicio de sesión exitoso ({$user->role}) desde {$request->ip()}", null, 'normal', $user->id);

            return redirect()->to($this->destinationFor($user))
                ->with('success', "Bienvenido(a), {$user->name}. Acceso autorizado para {$user->role}.");
        }

        AuditService::log('Auth', $user->id, 'LOGIN_FAILED', "Contraseña incorrecta para el usuario: {$user->email}", null, 'advertencia', $user->id);

        return back()->withInput()->with('error', 'Contraseña incorrecta. Verifique sus credenciales o use la recuperación por token.');
    }

    /**
     * Genera y despacha un token seguro de recuperación de contraseña institucional.
     * Formato del token en la interfaz de diseño: TK-8942-X
     */
    public function sendRecoveryToken(Request $request): RedirectResponse
    {
        $request->validate([
            'recovery_identifier' => ['required', 'string'],
        ], [
            'recovery_identifier.required' => 'Ingrese su correo institucional o número de empleado/matrícula.',
        ]);

        $input = trim($request->input('recovery_identifier'));
        $field = filter_var($input, FILTER_VALIDATE_EMAIL) ? 'email' : 'institutional_id';

        $user = User::where($field, $input)->first();

        if (!$user) {
            return back()->with('error', 'El identificador institucional ingresado no está registrado en el sistema.');
        }

        // Generar token con el patrón visual exacto: TK-####-X
        $tokenNumber = rand(1000, 9999);
        $tokenLetter = chr(rand(65, 90));
        $generatedToken = "TK-{$tokenNumber}-{$tokenLetter}";

        // Guardar token en tabla password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($generatedToken),
                'created_at' => now(),
            ]
        );

        AuditService::log('Auth', $user->id, 'TOKEN_GENERATED', "Token de recuperación emitido para {$user->email}: {$generatedToken}", null, 'advertencia', $user->id);

        return back()->with('token_sent', true)
                     ->with('demo_token', $generatedToken)
                     ->with('user_email', $user->email)
                     ->with('success', "Token de seguridad generado con éxito. Su código de autorización es: {$generatedToken}");
    }

    /**
     * Valida el token emitido y restablece la contraseña del usuario.
     */
    public function resetPassword(Request $request): RedirectResponse
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

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record) {
            return back()->with('error', 'No existe una solicitud de recuperación activa para esta cuenta.');
        }

        // Verificar validez del token
        $tokenInput = trim(strtoupper($request->token));
        $validToken = Hash::check($tokenInput, $record->token) || ($tokenInput === $record->token);

        if (!$validToken) {
            AuditService::log('Auth', null, 'TOKEN_INVALID', "Intento de restablecimiento con token inválido para: {$request->email}", null, 'advertencia');
            return back()->with('error', 'El token ingresado no es válido o ha expirado. Solicite uno nuevo.');
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('error', 'Usuario no localizado.');
        }

        $user->update([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        AuditService::log('Auth', $user->id, 'PASSWORD_RESET', "Contraseña restablecida exitosamente para {$user->email}", null, 'normal', $user->id);

        return redirect()->route('login')->with('success', 'Su contraseña ha sido actualizada con éxito. Inicie sesión con sus nuevas credenciales.');
    }

    /**
     * Cierra la sesión activa del operador.
     */
    public function logout(Request $request): RedirectResponse
    {
        $userId = Auth::id();
        $userEmail = Auth::user()?->email;

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
            User::ROLE_USUARIO => route('public.catalog'),
            User::ROLE_ENCARGADO_ACTIVOS => route('equipment.index'),
            User::ROLE_ENCARGADO_PRESTAMOS => route('loans.index'),
            User::ROLE_TECNICO => route('incidents.index'),
            default => route('dashboard'),
        };
    }
}


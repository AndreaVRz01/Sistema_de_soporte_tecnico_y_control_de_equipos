<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthenticationService
{
    public function __construct(private readonly ActivityLogService $activityLog)
    {
    }

    public function login(Request $request, array $credentials, bool $remember = false): User
    {
        $validated = Validator::make($credentials, [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ])->validate();

        if (!Auth::attempt([
            'email' => $validated['email'],
            'password' => $validated['password'],
            'status' => 'activo',
        ], $remember)) {
            throw new AuthenticationException('Las credenciales no son válidas o la cuenta no está activa.');
        }

        $request->session()->regenerate();
        $user = $request->user();
        $this->activityLog->record($user, 'sesion.iniciada', $user);

        return $user;
    }

    public function logout(Request $request): void
    {
        $user = $request->user();
        if ($user) {
            $this->activityLog->record($user, 'sesion.cerrada', $user);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
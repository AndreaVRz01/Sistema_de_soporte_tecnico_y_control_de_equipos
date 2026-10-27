<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        $allowedRoles = array_map(
            static fn (string $role): string => strtolower(trim($role)),
            $roles
        );

        if (!$user || !\in_array($user->role, $allowedRoles, true)) {
            abort(403, 'No tienes permiso para acceder a este recurso.');
        }

        return $next($request);
    }
}
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->withErrors(['email' => 'Su cuenta se encuentra inactiva.']);
        }

        if (!in_array($user->role, $roles)) {
            abort(403, 'Acceso denegado: No cuenta con los permisos necesarios para esta sección.');
        }

        return $next($request);
    }
}
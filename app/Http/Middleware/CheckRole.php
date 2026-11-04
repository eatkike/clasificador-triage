<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Valida que el rol del usuario autenticado coincida con el requerido.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Comparación sin distinción de mayúsculas/minúsculas
        if (strtolower($user->rol) !== strtolower($role)) {
            abort(403, 'Acceso denegado: No cuenta con los privilegios suficientes para esta sección.');
        }

        return $next($request);
    }
}
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRol
{
    public function handle(Request $request, Closure $next, string $rol = null)
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        if ($rol !== null) {
            $user = auth()->user();
            if ($user->rol->nombre !== $rol && $user->rol->nombre !== 'Admin') {
                abort(403, 'Acceso no autorizado.');
            }
        }

        return $next($request);
    }
}

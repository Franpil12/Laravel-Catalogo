<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Validar que el usuario esté autenticado y sea admin
        if ($request->user() && $request->user()->rol === 'admin') {
            return $next($request);
        }   

        return response()->json(['message' => 'Acceso denegado: solo administradores'], 403);
    }
}
<?php

namespace App\Http\Middleware;

use App\Models\SistemaSaas;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AutenticarSistemaSaas
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-Api-Key');

        $sistema = $apiKey ? SistemaSaas::where('api_key', $apiKey)->where('activo', true)->first() : null;

        if (! $sistema) {
            return response()->json(['error' => 'api key inválida'], 401);
        }

        $request->attributes->set('sistema_saas', $sistema);

        return $next($request);
    }
}

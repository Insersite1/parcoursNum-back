<?php
namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;

class JeuneMiddleware
{
    public function handle($request, Closure $next)
    {
        $user=JWTAuth::parseToken()->authenticate();
        if (!$user || $user->role->name!="Jeune") {
            return response()->json(['error' => 'Accès non autorisé.'], 403);
        }

        return $next($request);
    }
}

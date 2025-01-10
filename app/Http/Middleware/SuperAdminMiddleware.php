<?php

namespace App\Http\Middleware;
use Tymon\JWTAuth\Facades\JWTAuth;

use Closure;

class SuperAdminMiddleware
{
    public function handle($request, Closure $next)
    {
        // if (auth()->user() && auth()->user()->role_id == 1) {
        //     return $next($request);
        // }
        $user=JWTAuth::parseToken()->authenticate();
        if (!$user & $user->role_id==1) {
            return response()->json(['error' => 'Accès non autorisé.'], 403);
        }

        return $next($request);
    }
}

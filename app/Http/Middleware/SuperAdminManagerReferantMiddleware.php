<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;

class SuperAdminManagerReferantMiddleware
{
    public function handle($request, Closure $next)
    {
        /*if (Auth::check() && in_array(Auth::user()->role->name, ['super_admin', 'manager', 'referent'])) {
            return $next($request);
        }

        return response()->json(['error' => 'Accès non autorisé.'], 403);*/

        $user=JWTAuth::parseToken()->authenticate();
        if (!$user || ($user->role->name!="SuperAdmin" && $user->role->name!="SuperAdmin" && $user->role->name!="Referent")) {
            return response()->json(['error' => 'Accès non autorisé.'], 403);
        }

        return $next($request);
    }
}

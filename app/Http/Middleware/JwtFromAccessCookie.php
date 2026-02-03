<?php
// app/Http/Middleware/JwtFromAccessCookie.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtFromAccessCookie
{
    public function handle($request, Closure $next)
    {
        Log::debug("Route called....", ["route" => $request->route()]);
        if (!$request->hasCookie('access_token')) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        try {
            JWTAuth::setToken($request->cookie('access_token'));
            JWTAuth::authenticate();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        return $next($request);
    }
}

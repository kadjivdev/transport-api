<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class JwtAccessCookie
{
    public function handle($request, Closure $next)
    {
        if (!$request->hasCookie('access_token')) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // $request->headers->set(
        //     'Authorization',
        //     'Bearer ' . $request->cookie('access_token')
        // );

        Log::debug("JWT cokie called ....");

        try {
            $token = $request->cookie('access_token');

            Log::debug("JWT cokie called ....", ["cookie" => $token]);

            $user = JWTAuth::setToken($token)->authenticate();

            // VERY IMPORTANT
            auth()->setUser($user);
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Invalid or expired token',
                'message' => $e->getMessage()
            ], 401);
        }

        return $next($request);
    }
}

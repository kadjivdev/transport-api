<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\RefreshToken;
use App\Models\Role;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
// use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as ModelsRole;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): Response
    {
        $request->authenticate();
        $request->session()->regenerate();
        return response()->noContent();
    }

    /**
     * Custom login
     */

    public function login(Request $request)
    {
        Log::info("Trying to log in......");
        try {
            $request->validate(
                [
                    'email' => ['required', 'string', 'email'],
                    'password' => ['required', 'string'],
                ],
                [
                    'email.required' => 'L’adresse email est obligatoire.',
                    'email.string'   => 'L’adresse email doit être une chaîne de caractères.',
                    'email.email'    => 'Veuillez entrer une adresse email valide.',
                    'password.required' => 'Le mot de passe est obligatoire.',
                    'password.string'   => 'Le mot de passe doit être une chaîne de caractères.',
                ]
            );

            $credentials = $request->only('email', 'password');

            if (!$token = Auth::guard('api')->attempt($credentials)) {
                return response()->json([
                    'error' => 'Unauthorized'
                ], 401);
            }

            $user = Auth::guard('api')->user();
            $user['permissions'] = $user->getAllPermissions();
            $all_permissions = Permission::get(["id", "name", "description"]);
            $all_roles = Role::with("permissions")
                ->latest()->get();

            // Créer refresh token
            $refreshToken = RefreshToken::create([
                'user_id' => $user->id,
                'token' => hash('sha256', Str::random(64)),
                'expires_at' => now()->addMinute((int) env("JWT_REFRESH_TTL")),
            ]);

            /**
             * Création du cookie
             * */

            // Access Cookie
            $access_cookie = cookie(
                'access_token',                 // nom
                $token,                         // valeur
                (int) env("JWT_TTL"),               // durée en minutes
                '/',                            // path
                null,                           // domain
                false,                           // $secure,          // secure
                true,                           // httpOnly
                false,                          // raw
                'Lax',                         // $sameSite       // sameSite
            );

            // Refresh token
            $refresh_token = cookie(
                'refresh_token',                // nom
                $refreshToken->token,           // valeur
                (int) env("JWT_REFRESH_TTL"),   // durée en minutes
                '/',                            // path
                null,                           // domain
                false,                          // $secure,          // secure
                true,                           // httpOnly
                false,                          // raw
                'Lax',
            );

            Log::info("Connexion réussie avec succès!");
            Log::info("Les cookies : ", ["cookies" => request()->cookies->all()]);
            return response()->json([
                "message" => "Connexion réussie avec succès!",
                "user" => $user,
                "all_permissions" => $all_permissions,
                "all_roles" => $all_roles,
            ])
                ->withCookie($access_cookie)
                ->withCookie($refresh_token);
        } catch (ValidationException $e) {
            Log::error("Erreure de validation survenue lors de la connexion", ["error" => $e->errors()]);
            return response()->json(["errors" => $e->errors()], 422);
        } catch (Exception $e) {
            Log::error("Erreure d'exception survenue lors de la connexion", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    /**
     * Checking if access token existe in cookie
     */
    function verifyAccessToken(Request $request)
    {
        try {
            $accessToken = $request->cookie('access_token');
            // $refrehToken = $request->cookie('refresh_token');

            Log::info("Vérification du token access");
            Log::debug("Access token :", ["token" => $accessToken]);
            if (!$accessToken) {
                return response()->json(['error' => 'No access token'], 401);
            }

            $user = JWTAuth::setToken($accessToken)->getPayload($accessToken)->toArray();

            return response()->json([
                'message' => 'Access token is valid',
                'user' => $user,
            ], 200);
        } catch (JWTException $e) {
            Log::error("JWT Exception during token verification", ["error" => $e->getMessage()]);
            return response()->json(['error' => 'Invalid access token'], 401);
        } catch (Exception $e) {
            Log::error("Exception during token verification", ["error" => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Refreshing token
     */
    public function refresh(Request $request)
    {
        try {
            //code...
            Log::info("Refreshing du token refresh ...");
            $refreshTokenValue = $request->cookie('refresh_token');

            Log::debug("The refresh token ", ["token" => $refreshTokenValue]);
            Log::info("Les cookies : ", ["cookies" => request()->cookies->all()]);
            Log::info("Le header autorization : ", ["autorization" => request()->header('authorization')]);

            if (!$refreshTokenValue) {
                return response()->json(['error' => 'No refresh token'], 401);
            }

            $refreshToken = RefreshToken::where('token', $refreshTokenValue)
                ->where('expires_at', '>', now())
                ->first();

            if (!$refreshToken) {
                return response()->json(['error' => 'Invalid refresh token'], 401);
            }

            $user = $refreshToken->user;

            // Créer nouveau access token
            $accessToken = JWTAuth::fromUser($user);

            // $secure = app()->environment('production');
            // $sameSite = $secure ? 'None' : 'Lax';

            $accessToken = cookie(
                'access_token',
                $accessToken,
                (int) env("JWT_TTL"),
                '/',
                null,                           // domain
                false,                          // $secure,          // secure
                true,                           // httpOnly
                false,                          // raw
                'Lax',
            );

            return response()->json(['message' => 'Token refreshed'])
                ->withCookie($accessToken);
        } catch (JWTException $e) {
            Log::debug("Une erreure est survenue lors du refreh ", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()]);
        } catch (Exception $e) {
            Log::debug("Une erreure est survenue lors du refreh ", ["error" => $e->getMessage()]);
            return response()->json(["error" => $e->getMessage()]);
        }
    }

    /**
     * Custom logout
     */
    public function logout(Request $request)
    {
        try {
            Log::info("User desconnected successflly ...");

            if ($request->hasCookie('refresh_token')) {
                RefreshToken::where('token', $request->cookie('refresh_token'))->delete();
            }

            if ($request->hasCookie('access_token')) {
                JWTAuth::setToken($request->cookie('access_token'))->invalidate();
            }

            return response()->json(['message' => 'Logged out'])
                ->withCookie(Cookie::forget('access_token'))
                ->withCookie(Cookie::forget('refresh_token'));
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

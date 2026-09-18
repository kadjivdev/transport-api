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
            Log::info("Debut validation ......");

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
                Log::info("Unauthorized ......");
                return response()->json([
                    'error' => 'Unauthorized'
                ], 401);
            }

            $user = Auth::guard('api')->user()->load("roles");
            $user['permissions'] = $user->roles->flatMap->permissions;

            // ⚠️ À restreindre si possible (ex: seulement pour les users avec droit d'admin)
            $all_permissions = Permission::get(["id", "name", "description"]);
            $all_roles = Role::with("permissions")->latest()->get();

            // Créer refresh token : on génère un token en clair, on stocke son hash
            $plainRefreshToken = Str::random(64);

            $refreshToken = RefreshToken::create([
                'user_id' => $user->id,
                'token' => hash('sha256', $plainRefreshToken),
                'expires_at' => now()->addMinutes((int) config('jwt.refresh_ttl')),
            ]);

            $isProduction = app()->environment('production');

            /**
             * Création des cookies
             */

            // Access token : httpOnly pour empêcher le vol via XSS.
            // Le navigateur l'enverra automatiquement, pas besoin de le lire en JS.
            $access_cookie = cookie(
                'access_token',
                $token,
                (int) config('jwt.ttl'),
                '/',
                null,
                $isProduction,   // secure : true en prod (HTTPS)
                true,             // httpOnly
                false,
                'Lax',
            );

            // Refresh token : on envoie le token en clair, la DB ne garde que le hash
            $refresh_token = cookie(
                'refresh_token',
                $plainRefreshToken,
                (int) config('jwt.refresh_ttl'),
                '/',
                null,
                $isProduction,   // secure
                true,            // httpOnly
                false,
                'Lax',
            );

            Log::info("Connexion réussie avec succès!");

            return response()->json([
                "message" => "Connexion réussie avec succès!",
                "user" => $user,
                "all_permissions" => $all_permissions,
                "all_roles" => $all_roles,
            ])
                ->withCookie($access_cookie)
                ->withCookie($refresh_token);
        } catch (ValidationException $e) {
            Log::error("Erreur de validation survenue lors de la connexion", ["error" => $e->errors()]);
            return response()->json(["errors" => $e->errors()], 422);
        } catch (Exception $e) {
            Log::error("Erreur d'exception survenue lors de la connexion", ["error" => $e->getMessage()]);
            return response()->json(["error" => "Une erreur interne est survenue."], 500);
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
            Log::info("Refreshing du token refresh ...");

            $refreshTokenValue = $request->cookie('refresh_token');

            if (!$refreshTokenValue) {
                return response()->json(['error' => 'No refresh token'], 401);
            }

            $hashedToken = hash('sha256', $refreshTokenValue);

            $refreshToken = RefreshToken::where('token', $hashedToken)
                ->where('expires_at', '>', now())
                ->first();

            if (!$refreshToken) {
                Log::info("Refresh token invalide ou expiré.");
                return response()->json(['error' => 'Invalid refresh token'], 401);
            }

            $user = $refreshToken->user;

            // Rotation : on invalide l'ancien refresh token
            $refreshToken->delete();

            // Nouveau access token
            $newAccessToken = JWTAuth::fromUser($user);

            // Nouveau refresh token (rotation)
            $plainRefreshToken = Str::random(64);
            RefreshToken::create([
                'user_id' => $user->id,
                'token' => hash('sha256', $plainRefreshToken),
                'expires_at' => now()->addMinutes((int) config('jwt.refresh_ttl')),
            ]);

            $isProduction = app()->environment('production');

            $access_cookie = cookie(
                'access_token',
                $newAccessToken,
                (int) config('jwt.ttl'),
                '/',
                null,
                $isProduction,
                true,    // httpOnly
                false,
                'Lax',
            );

            $refresh_cookie = cookie(
                'refresh_token',
                $plainRefreshToken,
                (int) config('jwt.refresh_ttl'),
                '/',
                null,
                $isProduction,
                true,   // httpOnly
                false,
                'Lax',
            );

            Log::info("Token rafraîchi avec succès pour l'utilisateur {$user->id}.");

            return response()->json(['message' => 'Token refreshed'])
                ->withCookie($access_cookie)
                ->withCookie($refresh_cookie);
        } catch (JWTException $e) {
            Log::error("Erreur JWT lors du refresh", ["error" => $e->getMessage()]);
            return response()->json(["error" => "Impossible de rafraîchir le token."], 401);
        } catch (Exception $e) {
            Log::error("Erreur lors du refresh", ["error" => $e->getMessage()]);
            return response()->json(["error" => "Une erreur interne est survenue."], 500);
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

            return response()->json(['message' => 'Logged out'], 200)
                ->withCookie(Cookie::forget('access_token'))
                ->withCookie(Cookie::forget('refresh_token'));
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Me
     */
    public function me(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $user->load('roles');
        $user['permissions'] = $user->roles->flatMap->permissions;

        return response()->json(['user' => $user]);
    }
}

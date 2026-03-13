<?php

use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Crée un utilisateur (si nécessaire), génère un JWT et renvoie le
 * token. Les tests peuvent ensuite utiliser `withCookie('access_token',
 * $token)` pour authentifier leurs appels.
 *
 * @param  \App\Models\User|null  $user
 * @return string
 */
function jwtToken(User $user = null): string
{
    $user ??= User::factory()->create();
    return auth('api')->login($user);
}

test('Recuperer_tous_les_utilisateurs', function () {
    User::factory()->count(5)->create();            // donne des données à l’endpoint
    $user  = User::factory()->create();             // utilisateur qui fait la requête
    $token = jwtToken($user);

    $response = $this->withCookie('access_token', $token)
                     ->get('/api/v1/users');

    Log::debug('Test : tous les utilisateurs ', ['data' => $response->json()]);

    $response->assertStatus(200);
});

test('Recuperer_un_seul_utilisateur', function () {
    User::factory()->count(2)->create();
    $user  = User::first();
    $token = jwtToken($user);

    $response = $this->withCookie('access_token', $token)
                     ->get("/api/v1/users/{$user->id}");

    $response->assertStatus(200);
});

test('Creer_un_utilisateur', function () {
    $user  = User::factory()->create();
    $token = jwtToken($user);

    $data = [
        'name'                  => 'User test',
        'email'                 => 'test@gmail.com',
        'password'              => 'test@password',
        'password_confirmation' => 'test@password',
    ];

    $response = $this->withCookie('access_token', $token)
                     ->post('/api/v1/users', $data);

    Log::debug("Test : l'utilisateur crée ", ['user' => $response->json()]);

    $response->assertStatus(201);
});

test('Modifier_un_utilisateur', function () {
    User::factory()->count(2)->create();
    $user  = User::first();
    $token = jwtToken($user);

    $data = [
        'name'                  => 'User test',
        'email'                 => 'test@gmail.com',
        'password'              => 'test@password',
        'password_confirmation' => 'test@password',
    ];

    $response = $this->withCookie('access_token', $token)
                     ->patch("/api/v1/users/{$user->id}", $data);

    $response->assertStatus(200);
});

test('Supprimer_un_utilisateur', function () {
    User::factory()->count(2)->create();
    $user  = User::first();
    $token = jwtToken($user);

    $response = $this->withCookie('access_token', $token)
                     ->delete("/api/v1/users/{$user\->id}");

    $response->assertStatus(200);
});
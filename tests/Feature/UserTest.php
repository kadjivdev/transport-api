<?php

use App\Models\User;
use Illuminate\Support\Facades\Log;

test('Recuperer_tous_les_utilisateurs', function () {

    User::factory()
        ->count(5)
        ->create();

    $user = User::first();


    // Generate JWT token
    $token = auth('api')->login($user);

    // Make request with JWT in header
    $response = $this->withHeader('Authorization', "Bearer $token")
        ->get('/api/v1/users');

    // $response = $this->actingAs($user, 'api')
    //     ->get('/api/v1/users');

    Log::debug("Test : tous les utilisateurs ", ["data" => $response->json()]);

    $response
        ->assertStatus(200);
});

test("Recuperer_un_seul_utilisateur", function () {
    User::factory()
        ->count(2)
        ->create();
    $user = User::first();
    Log::debug("Tous les users :", ["data" => User::all()]);
    $response = $this->get("/api/v1/users/{$user->id}");
    $response->assertStatus(200);
});

test("Creer_un_utilisateur", function () {
    $data = [
        'name' => 'User test',
        'email' => 'test@gmail.com',
        'password' => 'test@password',
        'password_confirmation' => 'test@password',
    ];
    $response = $this->post("/api/v1/users", $data);
    Log::debug("Test : l'unitisateur crée ", ['user' => $response->json()]);
    $response->assertStatus(201);
});

test("Modifier_un_utilisateur", function () {
    User::factory()
        ->count(2)
        ->create();

    $user = User::first();

    $data = [
        'name' => 'User test',
        'email' => 'test@gmail.com',
        'password' => 'test@password',
        'password_confirmation' => 'test@password',
    ];

    $response = $this->patch("/api/v1/users/{$user->id}", $data);
    $response->assertStatus(200);
});

test("Supprimer_un_utilisateur", function () {
    User::factory()
        ->count(2)
        ->create();

    $user = User::first();

    $response = $this->delete("/api/v1/users/{$user->id}");
    $response->assertStatus(200);
});

<?php

use App\Models\User;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    $this->authenticatedUser = User::factory()->create();
    $loginResponse =  $this->postJson('/api/v1/login', [
        'email' => $this->authenticatedUser?->email,
        'password' => 'password',
    ]);

    $this->access_token = $loginResponse->json()['access_token'];
});

describe('', function () {
    it('users can authenticate via login route', function () {

        $loginResponse =  $this->postJson('/api/v1/login', [
            'email' => $this->authenticatedUser?->email,
            'password' => 'password',
        ]);

        $this->access_token = $loginResponse->json()['access_token'];
        Log::debug("Le response d'accès est : ", ["response" => $loginResponse->json()['access_token']]);

        $this->assertAuthenticated();
    })->after(function () {
        Log::debug("Le user connecté est : ", ["user" => $this->authenticatedUser]);
    });

    it('new users can register', function () {
        Log::debug("Le token est : ", ["access_token" => $this->access_token]);

        $response = $this->withCookie('access_token', $this->access_token)
            ->post('/api/v1/users', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        Log::debug("The response :", ["response" => $response->json()]);
        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        Log::debug("The response :", ["response" => $response->json()]);
    })->only();
})->group('Registration');

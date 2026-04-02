<?php

use App\Models\User;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    $this->authenticatedUser = User::factory()->create();
    $this->access_token = null;
});

describe('', function () {
    it('users can authenticate via login route', function () {

        $loginResponse =  $this->postJson('/api/v1/login', [
            'email' => $this->authenticatedUser?->email,
            'password' => 'password',
        ]);

        $cookies = $loginResponse->baseResponse->headers->getCookies();

        $accessToken = collect($cookies)
            ->firstWhere('Name', 'access_token')
            ?->getValue();
        $this->access_token = $accessToken;

        $this->assertAuthenticated();
    })->after(function () {
        Log::debug("Le user connecté est : ", ["user" => $this->authenticatedUser]);
    })->only();

    it('new users can register', function () {
        $response = $this->withCookie('access_token', $this->access_token)->post('/api/v1/users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        Log::debug("The response :", ["response" => $response->json()]);
    });
})->group('Registration');

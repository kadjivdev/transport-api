<?php

use App\Models\User;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    $this->authenticatedUser = User::factory()->create();
});

describe('Authentication\' tests', function () {
    it('users can authenticate via login route', function () {

        $response =  $this->postJson('/api/v1/login', [
            'email' => $this->authenticatedUser?->email,
            'password' => 'password',
        ]);

        Log::debug("The response :", ["user" => $response->json() ? $response->json()['user'] : null]);

        $this->authenticatedUser = $response->json()['user'];
        $this->assertAuthenticated();
    })->after(function () {
        Log::debug("Le user connecté est : ", ["user" => $this->authenticatedUser]);
    });

    it('users can not authenticate with invalid credentials', function () {
        $this->post('/api/v1/login', [
            'email' => $this->authenticatedUser?->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    });

    it('users can logout', function () {
        $response = $this->actingAs($this->authenticatedUser)->post('/api/v1/logout');
        // $this->assertGuest();

        $response->assertStatus(200);
        // $response->assertNoContent();
    });
})->group('Authentication');

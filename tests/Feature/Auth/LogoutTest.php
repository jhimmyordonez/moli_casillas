<?php

it('cierra sesión correctamente', function () {
    ['account' => $account, 'token' => $token] = createAuthenticatedAccount();

    // Verify token exists before logout
    $this->assertDatabaseCount('personal_access_tokens', 1);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/auth/logout');

    $response->assertSuccessful();

    // Verify token is gone from DB
    $this->assertDatabaseCount('personal_access_tokens', 0);

    // Clear auth state to force Sanctum to look in the DB for the next request
    auth()->forgetUser();

    // Token should be revoked — a NEW request with the same token should fail
    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/me')
        ->assertStatus(401);
});

it('devuelve 401 al cerrar sesión sin autenticación', function () {
    $this->postJson('/api/v1/auth/logout')
        ->assertStatus(401)
        ->assertJsonPath('errors.0.code', 'UNAUTHENTICATED');
});

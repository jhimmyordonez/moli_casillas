<?php

it('devuelve información del usuario autenticado', function () {
    ['account' => $account, 'token' => $token] = createAuthenticatedAccount();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/me');

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $account->id)
        ->assertJsonPath('data.usuario_auth_id', $account->usuario_auth_id);
});

it('devuelve 401 cuando no está autenticado', function () {
    $this->getJson('/api/v1/me')
        ->assertStatus(401)
        ->assertJsonPath('errors.0.code', 'UNAUTHENTICATED');
});

<?php

use App\Models\VersionTerminos;

it('devuelve términos y condiciones vigentes', function () {
    ['account' => $account, 'token' => $token] = createAuthenticatedAccount();
    $terminos = VersionTerminos::factory()->activa()->create();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/terms/current');

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $terminos->id)
        ->assertJsonPath('data.es_activo', true);
});

it('devuelve 404 cuando no hay términos activos', function () {
    ['token' => $token] = createAuthenticatedAccount();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/terms/current')
        ->assertStatus(404);
});

it('acepta términos exitosamente', function () {
    ['account' => $account, 'token' => $token] = createAuthenticatedAccount();
    $terminos = VersionTerminos::factory()->activa()->create();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/terms/accept', [
            'version_terminos_id' => $terminos->id,
        ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.mensaje', 'Términos aceptados correctamente.');

    $this->assertDatabaseHas('aceptaciones_terminos', [
        'version_terminos_id' => $terminos->id,
        'usuario_auth_id' => $account->usuario_auth_id,
    ]);
});

it('bloquea acceso a endpoints de casilla sin aceptación de términos', function () {
    ['account' => $account, 'token' => $token] = createAuthenticatedAccount();
    VersionTerminos::factory()->activa()->create();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/casilla/messages')
        ->assertStatus(403)
        ->assertJsonPath('errors.0.code', 'TERMS_NOT_ACCEPTED');
});

it('permite acceso a endpoints de casilla tras aceptación de términos', function () {
    ['account' => $account, 'token' => $token] = createAuthenticatedAccount();
    acceptTermsForAccount($account);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/casilla/messages')
        ->assertSuccessful();
});

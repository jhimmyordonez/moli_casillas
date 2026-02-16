<?php

use App\Models\Adjunto;
use App\Models\Mensaje;
use App\Models\Casilla;

it('muestra detalle del mensaje con adjuntos', function () {
    ['account' => $account, 'token' => $token] = createAuthenticatedAccount();
    acceptTermsForAccount($account);

    $mensaje = Mensaje::factory()->create(['casilla_id' => $account->id]);
    Adjunto::factory()->count(2)->create(['mensaje_id' => $mensaje->id]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/casilla/messages/{$mensaje->id}");

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $mensaje->id)
        ->assertJsonPath('data.asunto', $mensaje->asunto)
        ->assertJsonCount(2, 'data.adjuntos')
        ->assertJsonStructure([
            'data' => ['id', 'remitente_nombre', 'destinatario_nombre', 'asunto', 'cuerpo', 'etiqueta_estado', 'adjuntos', 'codigo_referencia', 'codigo_expediente'],
        ]);
});

it('devuelve 404 para mensaje de otra casilla', function () {
    ['account' => $account, 'token' => $token] = createAuthenticatedAccount();
    acceptTermsForAccount($account);

    $otherAccount = Casilla::factory()->create();
    $mensaje = Mensaje::factory()->create(['casilla_id' => $otherAccount->id]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/casilla/messages/{$mensaje->id}")
        ->assertStatus(404)
        ->assertJsonPath('errors.0.code', 'NOT_FOUND');
});

it('devuelve 404 para mensaje inexistente', function () {
    ['account' => $account, 'token' => $token] = createAuthenticatedAccount();
    acceptTermsForAccount($account);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/casilla/messages/00000000-0000-0000-0000-000000000000')
        ->assertStatus(404);
});

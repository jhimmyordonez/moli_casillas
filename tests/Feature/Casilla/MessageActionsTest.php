<?php

use App\Models\Casilla;
use App\Models\EventoMensaje;
use App\Models\Mensaje;

it('marca un mensaje como leído', function () {
    ['account' => $account, 'token' => $token] = createAuthenticatedAccount();
    acceptTermsForAccount($account);

    $mensaje = Mensaje::factory()->sinLeer()->create(['casilla_id' => $account->id]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/casilla/messages/{$mensaje->id}/read");

    $response->assertSuccessful()
        ->assertJsonPath('data.leido', true);

    $mensaje->refresh();
    expect($mensaje->leido_en)->not->toBeNull();

    // Verify event was created
    $this->assertDatabaseHas('eventos_mensajes', [
        'mensaje_id' => $mensaje->id,
        'tipo_evento' => 'READ',
    ]);
});

it('es idempotente al marcar como leído', function () {
    ['account' => $account, 'token' => $token] = createAuthenticatedAccount();
    acceptTermsForAccount($account);

    $mensaje = Mensaje::factory()->leido()->create(['casilla_id' => $account->id]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/casilla/messages/{$mensaje->id}/read")
        ->assertSuccessful();

    // Should not create duplicate events
    expect(EventoMensaje::where('mensaje_id', $mensaje->id)->count())->toBe(0);
});

it('archiva un mensaje', function () {
    ['account' => $account, 'token' => $token] = createAuthenticatedAccount();
    acceptTermsForAccount($account);

    $mensaje = Mensaje::factory()->leido()->create(['casilla_id' => $account->id]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/casilla/messages/{$mensaje->id}/archive");

    $response->assertSuccessful()
        ->assertJsonPath('data.leido', true);

    $mensaje->refresh();
    expect($mensaje->archivado_en)->not->toBeNull();

    $this->assertDatabaseHas('eventos_mensajes', [
        'mensaje_id' => $mensaje->id,
        'tipo_evento' => 'ARCHIVED',
    ]);
});

it('es idempotente al archivar', function () {
    ['account' => $account, 'token' => $token] = createAuthenticatedAccount();
    acceptTermsForAccount($account);

    $mensaje = Mensaje::factory()->archivado()->create(['casilla_id' => $account->id]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/casilla/messages/{$mensaje->id}/archive")
        ->assertSuccessful();

    expect(EventoMensaje::where('mensaje_id', $mensaje->id)->count())->toBe(0);
});

it('devuelve 404 al marcar como leído mensaje de otra casilla', function () {
    ['account' => $account, 'token' => $token] = createAuthenticatedAccount();
    acceptTermsForAccount($account);

    $otherAccount = Casilla::factory()->create();
    $mensaje = Mensaje::factory()->create(['casilla_id' => $otherAccount->id]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/casilla/messages/{$mensaje->id}/read")
        ->assertStatus(404);
});

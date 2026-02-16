<?php

use App\Models\Mensaje;
use App\Models\Casilla;

it('devuelve conteo de estados con ceros para casilla vacía', function () {
    ['account' => $account, 'token' => $token] = createAuthenticatedAccount();
    acceptTermsForAccount($account);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/casilla/messages/statuses');

    $response->assertSuccessful()
        ->assertJsonPath('data.cantidad_no_leidos', 0)
        ->assertJsonCount(4, 'data.estados');

    $estados = collect($response->json('data.estados'));
    expect($estados->firstWhere('codigo', 'UNREAD')['cantidad'])->toBe(0)
        ->and($estados->firstWhere('codigo', 'READ')['cantidad'])->toBe(0)
        ->and($estados->firstWhere('codigo', 'ARCHIVED')['cantidad'])->toBe(0)
        ->and($estados->firstWhere('codigo', 'NOTIFIED')['cantidad'])->toBe(0);
});

it('devuelve conteos correctos para estados variados', function () {
    ['account' => $account, 'token' => $token] = createAuthenticatedAccount();
    acceptTermsForAccount($account);

    // Create messages with different statuses
    Mensaje::factory()->count(5)->sinLeer()->create(['casilla_id' => $account->id]);
    Mensaje::factory()->count(3)->notificado()->create(['casilla_id' => $account->id]);
    Mensaje::factory()->count(7)->leido()->create(['casilla_id' => $account->id]);
    Mensaje::factory()->count(2)->archivado()->create(['casilla_id' => $account->id]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/casilla/messages/statuses');

    $response->assertSuccessful();

    $estados = collect($response->json('data.estados'));
    expect($estados->firstWhere('codigo', 'UNREAD')['cantidad'])->toBe(5)
        ->and($estados->firstWhere('codigo', 'NOTIFIED')['cantidad'])->toBe(3)
        ->and($estados->firstWhere('codigo', 'READ')['cantidad'])->toBe(7)
        ->and($estados->firstWhere('codigo', 'ARCHIVED')['cantidad'])->toBe(2);

    // cantidad_no_leidos includes both UNREAD and NOTIFIED
    expect($response->json('data.cantidad_no_leidos'))->toBe(8);
});

it('devuelve etiquetas correctas en español', function () {
    ['account' => $account, 'token' => $token] = createAuthenticatedAccount();
    acceptTermsForAccount($account);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/casilla/messages/statuses');

    $estados = collect($response->json('data.estados'));
    expect($estados->firstWhere('codigo', 'UNREAD')['etiqueta'])->toBe('SIN LEER')
        ->and($estados->firstWhere('codigo', 'READ')['etiqueta'])->toBe('LEÍDO')
        ->and($estados->firstWhere('codigo', 'ARCHIVED')['etiqueta'])->toBe('ARCHIVADO')
        ->and($estados->firstWhere('codigo', 'NOTIFIED')['etiqueta'])->toBe('NOTIFICADO');
});

it('no cuenta mensajes de otras casillas', function () {
    ['account' => $account, 'token' => $token] = createAuthenticatedAccount();
    acceptTermsForAccount($account);

    // Messages for current user
    Mensaje::factory()->count(3)->sinLeer()->create(['casilla_id' => $account->id]);

    // Messages for another user (should not be counted)
    $otherAccount = Casilla::factory()->create();
    Mensaje::factory()->count(10)->sinLeer()->create(['casilla_id' => $otherAccount->id]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/casilla/messages/statuses');

    $estados = collect($response->json('data.estados'));
    expect($estados->firstWhere('codigo', 'UNREAD')['cantidad'])->toBe(3);
    expect($response->json('data.cantidad_no_leidos'))->toBe(3);
});

it('actualiza conteos después de marcar un mensaje como leído', function () {
    ['account' => $account, 'token' => $token] = createAuthenticatedAccount();
    acceptTermsForAccount($account);

    $mensajes = Mensaje::factory()->count(3)->sinLeer()->create(['casilla_id' => $account->id]);

    // Verify initial counts
    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/casilla/messages/statuses');

    $estados = collect($response->json('data.estados'));
    expect($estados->firstWhere('codigo', 'UNREAD')['cantidad'])->toBe(3);

    // Mark one as read
    $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/casilla/messages/{$mensajes[0]->id}/read")
        ->assertSuccessful();

    // Verify updated counts
    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/casilla/messages/statuses');

    $estados = collect($response->json('data.estados'));
    expect($estados->firstWhere('codigo', 'UNREAD')['cantidad'])->toBe(2)
        ->and($estados->firstWhere('codigo', 'READ')['cantidad'])->toBe(1);
});

it('actualiza conteos después de archivar un mensaje', function () {
    ['account' => $account, 'token' => $token] = createAuthenticatedAccount();
    acceptTermsForAccount($account);

    $mensajes = Mensaje::factory()->count(2)->leido()->create(['casilla_id' => $account->id]);

    // Archive one
    $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/casilla/messages/{$mensajes[0]->id}/archive")
        ->assertSuccessful();

    // Verify counts
    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/casilla/messages/statuses');

    $estados = collect($response->json('data.estados'));
    expect($estados->firstWhere('codigo', 'READ')['cantidad'])->toBe(1)
        ->and($estados->firstWhere('codigo', 'ARCHIVED')['cantidad'])->toBe(1);
});

it('requiere autenticación', function () {
    $this->getJson('/api/v1/casilla/messages/statuses')
        ->assertStatus(401);
});

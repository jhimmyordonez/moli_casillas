<?php

use App\Models\Mensaje;
use App\Models\Casilla;

it('devuelve lista vacía para casilla sin mensajes', function () {
    ['account' => $account, 'token' => $token] = createAuthenticatedAccount();
    acceptTermsForAccount($account);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/casilla/messages');

    $response->assertSuccessful()
        ->assertJsonPath('meta.total', 0)
        ->assertJsonPath('data', []);
});

it('devuelve mensajes paginados', function () {
    ['account' => $account, 'token' => $token] = createAuthenticatedAccount();
    acceptTermsForAccount($account);

    Mensaje::factory()->count(20)->create(['casilla_id' => $account->id]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/casilla/messages?per_page=5');

    $response->assertSuccessful()
        ->assertJsonCount(5, 'data')
        ->assertJsonPath('meta.per_page', 5)
        ->assertJsonPath('meta.total', 20)
        ->assertJsonPath('meta.total_pages', 4);
});

it('devuelve etiqueta_estado correcta en lista', function () {
    ['account' => $account, 'token' => $token] = createAuthenticatedAccount();
    acceptTermsForAccount($account);

    Mensaje::factory()->sinLeer()->create(['casilla_id' => $account->id, 'registrado_en' => now()]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/casilla/messages');

    $response->assertSuccessful();
    expect($response->json('data.0.etiqueta_estado'))->toBe('SIN LEER');
});

it('filtra por etiqueta_estado', function () {
    ['account' => $account, 'token' => $token] = createAuthenticatedAccount();
    acceptTermsForAccount($account);

    Mensaje::factory()->count(3)->sinLeer()->create(['casilla_id' => $account->id]);
    Mensaje::factory()->count(2)->leido()->create(['casilla_id' => $account->id]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/casilla/messages?etiqueta_estado=SIN+LEER');

    $response->assertSuccessful()
        ->assertJsonPath('meta.total', 3);
});

it('filtra por asunto', function () {
    ['account' => $account, 'token' => $token] = createAuthenticatedAccount();
    acceptTermsForAccount($account);

    Mensaje::factory()->create(['casilla_id' => $account->id, 'asunto' => 'Resolución de Multa']);
    Mensaje::factory()->create(['casilla_id' => $account->id, 'asunto' => 'Otro Asunto']);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/casilla/messages?asunto=Multa');

    $response->assertSuccessful()
        ->assertJsonPath('meta.total', 1);
});

it('filtra por rango de fechas', function () {
    ['account' => $account, 'token' => $token] = createAuthenticatedAccount();
    acceptTermsForAccount($account);

    Mensaje::factory()->create([
        'casilla_id' => $account->id,
        'registrado_en' => '2026-01-15 10:00:00',
    ]);
    Mensaje::factory()->create([
        'casilla_id' => $account->id,
        'registrado_en' => '2026-02-15 10:00:00',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/casilla/messages?fecha_desde=2026-02-01&fecha_hasta=2026-02-28');

    $response->assertSuccessful()
        ->assertJsonPath('meta.total', 1);
});

it('no devuelve mensajes de otras casillas', function () {
    ['account' => $account, 'token' => $token] = createAuthenticatedAccount();
    acceptTermsForAccount($account);

    $otherAccount = Casilla::factory()->create();
    Mensaje::factory()->count(5)->create(['casilla_id' => $otherAccount->id]);
    Mensaje::factory()->count(2)->create(['casilla_id' => $account->id]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/casilla/messages');

    $response->assertSuccessful()
        ->assertJsonPath('meta.total', 2);
});

it('incluye acciones en la respuesta', function () {
    ['account' => $account, 'token' => $token] = createAuthenticatedAccount();
    acceptTermsForAccount($account);

    Mensaje::factory()->sinLeer()->create(['casilla_id' => $account->id]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/casilla/messages');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [['id', 'remitente_nombre', 'destinatario_nombre', 'asunto', 'registrado_en', 'etiqueta_estado', 'acciones' => ['puede_ver', 'puede_descargar', 'puede_marcar_leido', 'puede_archivar']]],
        ]);
});

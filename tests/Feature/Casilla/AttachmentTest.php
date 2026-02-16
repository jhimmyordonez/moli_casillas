<?php

use App\Models\Adjunto;
use App\Models\Mensaje;
use App\Models\Casilla;
use Illuminate\Support\Facades\Storage;

it('lista adjuntos de un mensaje', function () {
    ['account' => $account, 'token' => $token] = createAuthenticatedAccount();
    acceptTermsForAccount($account);

    $mensaje = Mensaje::factory()->create(['casilla_id' => $account->id]);
    Adjunto::factory()->count(3)->create(['mensaje_id' => $mensaje->id]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/casilla/messages/{$mensaje->id}/attachments");

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('devuelve 404 para adjuntos de mensaje de otra casilla', function () {
    ['account' => $account, 'token' => $token] = createAuthenticatedAccount();
    acceptTermsForAccount($account);

    $otherAccount = Casilla::factory()->create();
    $mensaje = Mensaje::factory()->create(['casilla_id' => $otherAccount->id]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/casilla/messages/{$mensaje->id}/attachments")
        ->assertStatus(404);
});

it('descarga un adjunto y crea evento', function () {
    Storage::fake('local');
    ['account' => $account, 'token' => $token] = createAuthenticatedAccount();
    acceptTermsForAccount($account);

    $mensaje = Mensaje::factory()->create(['casilla_id' => $account->id]);
    $adjunto = Adjunto::factory()->create([
        'mensaje_id' => $mensaje->id,
        'ruta_almacenamiento' => 'test-file.txt',
        'nombre_archivo' => 'test-file.txt',
        'tipo_mime' => 'text/plain',
    ]);
    Storage::disk('local')->put('test-file.txt', 'content');

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/casilla/attachments/{$adjunto->id}/download");

    $response->assertSuccessful();

    $this->assertDatabaseHas('eventos_mensajes', [
        'mensaje_id' => $mensaje->id,
        'tipo_evento' => 'DOWNLOADED',
    ]);
});

it('devuelve 404 para adjunto de otra casilla', function () {
    ['account' => $account, 'token' => $token] = createAuthenticatedAccount();
    acceptTermsForAccount($account);

    $otherAccount = Casilla::factory()->create();
    $mensaje = Mensaje::factory()->create(['casilla_id' => $otherAccount->id]);
    $adjunto = Adjunto::factory()->create(['mensaje_id' => $mensaje->id]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/casilla/attachments/{$adjunto->id}/download")
        ->assertStatus(404);
});

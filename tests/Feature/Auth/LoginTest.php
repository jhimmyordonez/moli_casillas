<?php

use App\Models\Casilla;

it('inicia sesión y crea una nueva casilla', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'usuario_auth_id' => '22222222-2222-2222-2222-222222222222',
        'tipo_documento' => 'DNI',
        'numero_documento' => '87654321',
        'nombre_mostrar' => 'María López',
        'email' => 'maria@example.com',
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => ['cuenta' => ['id', 'usuario_auth_id', 'tipo_documento', 'numero_documento'], 'token', 'tipo_token'],
            'meta',
            'errors',
        ]);

    $this->assertDatabaseHas('casillas', [
        'usuario_auth_id' => '22222222-2222-2222-2222-222222222222',
        'numero_documento' => '87654321',
    ]);
});

it('devuelve cuenta existente en inicio de sesión repetido', function () {
    $casilla = Casilla::factory()->create([
        'usuario_auth_id' => '33333333-3333-3333-3333-333333333333',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'usuario_auth_id' => '33333333-3333-3333-3333-333333333333',
        'tipo_documento' => 'DNI',
        'numero_documento' => '11111111',
        'nombre_mostrar' => 'Test User',
    ]);

    $response->assertSuccessful();

    expect(Casilla::where('usuario_auth_id', '33333333-3333-3333-3333-333333333333')->count())->toBe(1);
});

it('valida la petición de inicio de sesión', function () {
    $response = $this->postJson('/api/v1/auth/login', []);

    $response->assertStatus(422)
        ->assertJsonStructure(['data', 'meta', 'errors']);
});

it('rechaza uuid inválido para usuario_auth_id', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'usuario_auth_id' => 'not-a-uuid',
        'tipo_documento' => 'DNI',
        'numero_documento' => '12345678',
        'nombre_mostrar' => 'Test',
    ]);

    $response->assertStatus(422);
});

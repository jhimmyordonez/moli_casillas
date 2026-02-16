<?php

namespace Database\Seeders;

use App\Enums\EventType;
use App\Enums\MessageStatusLabel;
use App\Models\Adjunto;
use App\Models\Casilla;
use App\Models\EventoMensaje;
use App\Models\AceptacionTerminos;
use App\Models\VersionTerminos;
use App\Models\Mensaje;
use Illuminate\Database\Seeder;

class CasillaSeeder extends Seeder
{
    private const TEST_AUTH_USER_ID = '11111111-1111-1111-1111-111111111111';

    public function run(): void
    {
        // 1. Create test casilla account
        $casilla = Casilla::query()->create([
            'usuario_auth_id' => self::TEST_AUTH_USER_ID,
            'tipo_documento' => 'DNI',
            'numero_documento' => '12345678',
            'nombre_mostrar' => 'Juan Pérez García',
            'email' => 'juan.perez@example.com',
            'estado' => 'ACTIVO',
        ]);

        // 2. Create active terms version
        $terminos = VersionTerminos::query()->create([
            'version' => '1.0.0',
            'contenido_html' => '<h1>Términos y Condiciones de Uso</h1>'
                .'<p>Al utilizar el servicio de Casilla Electrónica de la Municipalidad, usted acepta '
                .'los siguientes términos y condiciones de uso...</p>'
                .'<h2>1. Objeto del Servicio</h2>'
                .'<p>La Casilla Electrónica es un domicilio digital que permite recibir notificaciones '
                .'administrativas de manera electrónica.</p>',
            'es_activo' => true,
            'publicado_en' => now(),
        ]);

        // 3. Accept terms for test user
        AceptacionTerminos::query()->create([
            'version_terminos_id' => $terminos->id,
            'usuario_auth_id' => self::TEST_AUTH_USER_ID,
            'aceptado_en' => now(),
            'ip' => '127.0.0.1',
        ]);

        // 4. Create 40 messages with varied statuses
        // 15 SIN LEER (unread)
        Mensaje::factory()
            ->count(15)
            ->sinLeer()
            ->create([
                'casilla_id' => $casilla->id,
                'destinatario_nombre' => $casilla->nombre_mostrar,
                'destinatario_tipo_doc' => $casilla->tipo_documento->value,
                'destinatario_num_doc' => $casilla->numero_documento,
            ]);

        // 5 NOTIFICADO
        Mensaje::factory()
            ->count(5)
            ->notificado()
            ->create([
                'casilla_id' => $casilla->id,
                'destinatario_nombre' => $casilla->nombre_mostrar,
                'destinatario_tipo_doc' => $casilla->tipo_documento->value,
                'destinatario_num_doc' => $casilla->numero_documento,
            ]);

        // 10 LEÍDO
        $mensajesLeidos = Mensaje::factory()
            ->count(10)
            ->leido()
            ->create([
                'casilla_id' => $casilla->id,
                'destinatario_nombre' => $casilla->nombre_mostrar,
                'destinatario_tipo_doc' => $casilla->tipo_documento->value,
                'destinatario_num_doc' => $casilla->numero_documento,
            ]);

        // 10 ARCHIVADO
        $mensajesArchivados = Mensaje::factory()
            ->count(10)
            ->archivado()
            ->create([
                'casilla_id' => $casilla->id,
                'destinatario_nombre' => $casilla->nombre_mostrar,
                'destinatario_tipo_doc' => $casilla->tipo_documento->value,
                'destinatario_num_doc' => $casilla->numero_documento,
            ]);

        // 5. Add events for read and archived messages
        foreach ($mensajesLeidos as $mensaje) {
            EventoMensaje::query()->create([
                'mensaje_id' => $mensaje->id,
                'tipo_evento' => EventType::Read,
                'ocurrido_en' => $mensaje->leido_en,
                'actor_usuario_id' => self::TEST_AUTH_USER_ID,
                'ip' => '127.0.0.1',
                'user_agent' => 'Seeder',
            ]);
        }

        foreach ($mensajesArchivados as $mensaje) {
            EventoMensaje::query()->create([
                'mensaje_id' => $mensaje->id,
                'tipo_evento' => EventType::Archived,
                'ocurrido_en' => $mensaje->archivado_en,
                'actor_usuario_id' => self::TEST_AUTH_USER_ID,
                'ip' => '127.0.0.1',
                'user_agent' => 'Seeder',
            ]);
        }

        // 6. Add attachments to first 10 messages
        $mensajesConAdjuntos = Mensaje::query()
            ->where('casilla_id', $casilla->id)
            ->orderByDesc('registrado_en')
            ->take(10)
            ->get();

        foreach ($mensajesConAdjuntos as $mensaje) {
            $attachmentCount = fake()->numberBetween(1, 3);
            Adjunto::factory()
                ->count($attachmentCount)
                ->create(['mensaje_id' => $mensaje->id]);
        }
    }
}

<?php

namespace Database\Factories;

use App\Enums\EventType;
use App\Models\EventoMensaje;
use App\Models\Mensaje;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<EventoMensaje> */
class EventoMensajeFactory extends Factory
{
    protected $model = EventoMensaje::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'mensaje_id' => Mensaje::factory(),
            'tipo_evento' => EventType::Deposited,
            'ocurrido_en' => now(),
            'actor_usuario_id' => Str::uuid()->toString(),
            'ip' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'metadatos' => null,
        ];
    }
}

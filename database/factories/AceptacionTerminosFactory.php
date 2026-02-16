<?php

namespace Database\Factories;

use App\Models\AceptacionTerminos;
use App\Models\VersionTerminos;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<AceptacionTerminos> */
class AceptacionTerminosFactory extends Factory
{
    protected $model = AceptacionTerminos::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'version_terminos_id' => VersionTerminos::factory(),
            'usuario_auth_id' => Str::uuid()->toString(),
            'aceptado_en' => now(),
            'ip' => fake()->ipv4(),
        ];
    }
}

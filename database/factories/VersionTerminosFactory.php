<?php

namespace Database\Factories;

use App\Models\VersionTerminos;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<VersionTerminos> */
class VersionTerminosFactory extends Factory
{
    protected $model = VersionTerminos::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'version' => fake()->semver(),
            'contenido_html' => '<h1>Términos y Condiciones</h1><p>'.fake()->paragraphs(5, true).'</p>',
            'es_activo' => false,
            'publicado_en' => now(),
        ];
    }

    public function activa(): static
    {
        return $this->state([
            'es_activo' => true,
        ]);
    }
}

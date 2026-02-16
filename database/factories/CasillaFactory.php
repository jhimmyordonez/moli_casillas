<?php

namespace Database\Factories;

use App\Enums\AccountStatus;
use App\Enums\DocType;
use App\Models\Casilla;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Casilla> */
class CasillaFactory extends Factory
{
    protected $model = Casilla::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'usuario_auth_id' => Str::uuid()->toString(),
            'tipo_documento' => fake()->randomElement(DocType::cases()),
            'numero_documento' => fake()->numerify('########'),
            'nombre_mostrar' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'estado' => AccountStatus::Active,
        ];
    }

    public function suspendida(): static
    {
        return $this->state(['estado' => AccountStatus::Suspended]);
    }
}

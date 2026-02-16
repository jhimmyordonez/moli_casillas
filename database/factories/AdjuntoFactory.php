<?php

namespace Database\Factories;

use App\Models\Adjunto;
use App\Models\Mensaje;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Adjunto> */
class AdjuntoFactory extends Factory
{
    protected $model = Adjunto::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $filename = fake()->word().'.'.fake()->randomElement(['pdf', 'docx', 'xlsx', 'jpg', 'png']);
        
        $mimeType = match (pathinfo($filename, PATHINFO_EXTENSION)) {
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            default => 'application/octet-stream',
        };

        return [
            'mensaje_id' => Mensaje::factory(),
            'nombre_archivo' => $filename,
            'tipo_mime' => $mimeType,
            'tamano_bytes' => fake()->numberBetween(1024, 5_242_880),
            'checksum_sha256' => hash('sha256', Str::random(40)),
            'driver_almacenamiento' => 'local',
            'ruta_almacenamiento' => 'attachments/'.Str::uuid().'/'.$filename,
            'subido_en' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }
}

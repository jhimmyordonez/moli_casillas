<?php

namespace Database\Factories;

use App\Enums\DocType;
use App\Enums\MessageStatusCode;
use App\Enums\MessageStatusLabel;
use App\Models\Casilla;
use App\Models\Mensaje;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Mensaje> */
class MensajeFactory extends Factory
{
    protected $model = Mensaje::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'casilla_id' => Casilla::factory(),
            'remitente_nombre' => fake()->name(),
            'remitente_entidad' => fake()->randomElement([
                'Gerencia de Desarrollo Urbano',
                'Sub Gerencia de Licencias',
                'Gerencia de Administración Tributaria',
                'Gerencia de Servicios a la Ciudad',
                'Sub Gerencia de Fiscalización',
                null,
            ]),
            'destinatario_nombre' => fake()->name(),
            'destinatario_tipo_doc' => fake()->randomElement(DocType::cases())->value,
            'destinatario_num_doc' => fake()->numerify('########'),
            'asunto' => fake()->randomElement([
                'Notificación de Resolución de Multa',
                'Requerimiento de Documentación',
                'Resolución de Licencia de Funcionamiento',
                'Notificación de Fiscalización',
                'Citación para Audiencia Administrativa',
                'Resolución de Expediente',
                'Aviso de Deuda Tributaria',
                'Notificación de Obra',
            ]),
            'cuerpo' => fake()->paragraphs(3, true),
            'registrado_en' => fake()->dateTimeBetween('-6 months', 'now'),
            'codigo_estado' => MessageStatusCode::Deposited,
            'etiqueta_estado' => MessageStatusLabel::SinLeer->value,
            'notificado_en' => null,
            'leido_en' => null,
            'archivado_en' => null,
            'codigo_referencia' => 'EXP-'.fake()->numerify('####-####'),
            'codigo_expediente' => fake()->optional(0.5)->numerify('####-####-MDLM'),
            'destacado' => fake()->boolean(20),
        ];
    }

    public function sinLeer(): static
    {
        return $this->state([
            'codigo_estado' => MessageStatusCode::Deposited,
            'etiqueta_estado' => MessageStatusLabel::SinLeer->value,
            'leido_en' => null,
            'archivado_en' => null,
            'notificado_en' => null,
        ]);
    }

    public function notificado(): static
    {
        return $this->state([
            'codigo_estado' => MessageStatusCode::Notified,
            'etiqueta_estado' => MessageStatusLabel::Notificado->value,
            'notificado_en' => fake()->dateTimeBetween('-3 months', 'now'),
            'leido_en' => null,
            'archivado_en' => null,
        ]);
    }

    public function leido(): static
    {
        return $this->state([
            'codigo_estado' => MessageStatusCode::Read,
            'etiqueta_estado' => MessageStatusLabel::Leido->value,
            'leido_en' => fake()->dateTimeBetween('-2 months', 'now'),
            'archivado_en' => null,
        ]);
    }

    public function archivado(): static
    {
        return $this->state([
            'codigo_estado' => MessageStatusCode::Archived,
            'etiqueta_estado' => MessageStatusLabel::Archivado->value,
            'leido_en' => fake()->dateTimeBetween('-3 months', '-1 month'),
            'archivado_en' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }
}

<?php

namespace App\Models;

use App\Enums\EventType;
// use Database\Factories\EventoMensajeFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="EventoMensaje",
 *     title="EventoMensaje",
 *     description="Evento registrado para un mensaje",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="mensaje_id", type="string", format="uuid"),
 *     @OA\Property(property="tipo_evento", type="string"),
 *     @OA\Property(property="ocurrido_en", type="string", format="date-time"),
 *     @OA\Property(property="actor_usuario_id", type="string", format="uuid", nullable=true),
 *     @OA\Property(property="ip", type="string", nullable=true),
 *     @OA\Property(property="user_agent", type="string", nullable=true),
 *     @OA\Property(property="metadatos", type="object", nullable=true)
 * )
 */
class EventoMensaje extends Model
{
    /** @use HasFactory<\Database\Factories\EventoMensajeFactory> */
    use HasFactory, HasUuids;

    protected $table = 'eventos_mensajes';

    public $timestamps = false;

    protected $fillable = [
        'mensaje_id',
        'tipo_evento',
        'ocurrido_en',
        'actor_usuario_id',
        'ip',
        'user_agent',
        'metadatos',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tipo_evento' => EventType::class,
            'ocurrido_en' => 'datetime',
            'metadatos' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Mensaje, $this>
     */
    public function mensaje(): BelongsTo
    {
        return $this->belongsTo(Mensaje::class, 'mensaje_id');
    }
}

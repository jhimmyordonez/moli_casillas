<?php

namespace App\Models;

use App\Enums\MessageStatusCode;
use App\Enums\MessageStatusLabel;
// use Database\Factories\MensajeFactory; // Will be MensajeFactory later
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Mensaje",
 *     title="Mensaje",
 *     description="Mensaje de usuario",
 *
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="casilla_id", type="string", format="uuid"),
 *     @OA\Property(property="remitente_nombre", type="string"),
 *     @OA\Property(property="remitente_entidad", type="string"),
 *     @OA\Property(property="destinatario_nombre", type="string"),
 *     @OA\Property(property="destinatario_tipo_doc", type="string"),
 *     @OA\Property(property="destinatario_num_doc", type="string"),
 *     @OA\Property(property="asunto", type="string"),
 *     @OA\Property(property="cuerpo", type="string"),
 *     @OA\Property(property="registrado_en", type="string", format="date-time"),
 *     @OA\Property(property="codigo_estado", type="string"),
 *     @OA\Property(property="etiqueta_estado", type="string"),
 *     @OA\Property(property="notificado_en", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="leido_en", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="archivado_en", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="codigo_referencia", type="string", nullable=true),
 *     @OA\Property(property="codigo_expediente", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Mensaje extends Model
{
    /** @use HasFactory<\Database\Factories\MensajeFactory> */
    use HasFactory, HasUuids;

    protected $table = 'mensajes';

    protected $fillable = [
        'casilla_id',
        'remitente_nombre',
        'remitente_entidad',
        'destinatario_nombre',
        'destinatario_tipo_doc',
        'destinatario_num_doc',
        'asunto',
        'cuerpo',
        'registrado_en',
        'codigo_estado',
        'etiqueta_estado',
        'notificado_en',
        'leido_en',
        'archivado_en',
        'destacado',
        'codigo_referencia',
        'codigo_expediente',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'codigo_estado' => MessageStatusCode::class,
            'registrado_en' => 'datetime',
            'notificado_en' => 'datetime',
            'leido_en' => 'datetime',
            'archivado_en' => 'datetime',
            'destacado' => 'boolean',
        ];
    }

    /**
     * Compute the user-visible status label from timestamps.
     *
     * @return Attribute<string, never>
     */
    protected function etiquetaEstadoComputada(): Attribute
    {
        return Attribute::get(function (): string {
            if ($this->archivado_en !== null) {
                return MessageStatusLabel::Archivado->value;
            }

            if ($this->leido_en !== null) {
                return MessageStatusLabel::Leido->value;
            }

            if ($this->notificado_en !== null) {
                return MessageStatusLabel::Notificado->value;
            }

            return MessageStatusLabel::SinLeer->value;
        });
    }

    /**
     * @return BelongsTo<Casilla, $this>
     */
    public function casilla(): BelongsTo
    {
        return $this->belongsTo(Casilla::class, 'casilla_id');
    }

    /**
     * @return HasMany<Adjunto, $this>
     */
    public function adjuntos(): HasMany
    {
        return $this->hasMany(Adjunto::class, 'mensaje_id');
    }

    /**
     * @return HasMany<EventoMensaje, $this>
     */
    public function eventos(): HasMany
    {
        return $this->hasMany(EventoMensaje::class, 'mensaje_id');
    }

    /**
     * @param  Builder<Mensaje>  $query
     * @return Builder<Mensaje>
     */
    public function scopeParaCasilla(Builder $query, string $casillaId): Builder
    {
        return $query->where('casilla_id', $casillaId);
    }

    /**
     * Filter by computed status label using timestamp columns.
     *
     * @param  Builder<Mensaje>  $query
     * @return Builder<Mensaje>
     */
    public function scopeConFiltroEstado(Builder $query, string $etiquetaEstado): Builder
    {
        return match ($etiquetaEstado) {
            MessageStatusLabel::Archivado->value => $query->whereNotNull('archivado_en'),
            MessageStatusLabel::Leido->value => $query->whereNotNull('leido_en')->whereNull('archivado_en'),
            MessageStatusLabel::Notificado->value => $query->whereNotNull('notificado_en')->whereNull('leido_en')->whereNull('archivado_en'),
            MessageStatusLabel::SinLeer->value => $query->whereNull('leido_en')->whereNull('archivado_en')->whereNull('notificado_en'),
            default => $query,
        };
    }
}

<?php

namespace App\Models;

// use Database\Factories\AdjuntoFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Adjunto",
 *     title="Adjunto",
 *     description="Adjunto de mensaje",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="mensaje_id", type="string", format="uuid"),
 *     @OA\Property(property="nombre_archivo", type="string"),
 *     @OA\Property(property="tipo_mime", type="string"),
 *     @OA\Property(property="tamano_bytes", type="integer"),
 *     @OA\Property(property="subido_en", type="string", format="date-time")
 * )
 */
class Adjunto extends Model
{
    /** @use HasFactory<\Database\Factories\AdjuntoFactory> */
    use HasFactory, HasUuids;

    protected $table = 'adjuntos';

    public $timestamps = false;

    protected $fillable = [
        'mensaje_id',
        'nombre_archivo',
        'tipo_mime',
        'tamano_bytes',
        'checksum_sha256',
        'driver_almacenamiento',
        'ruta_almacenamiento',
        'subido_en',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tamano_bytes' => 'integer',
            'subido_en' => 'datetime',
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

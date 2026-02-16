<?php

namespace App\Models;

// use Database\Factories\AceptacionTerminosFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="AceptacionTerminos",
 *     title="AceptacionTerminos",
 *     description="Aceptación de términos y condiciones",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="version_terminos_id", type="string", format="uuid"),
 *     @OA\Property(property="usuario_auth_id", type="string", format="uuid"),
 *     @OA\Property(property="aceptado_en", type="string", format="date-time"),
 *     @OA\Property(property="ip", type="string", nullable=true)
 * )
 */
class AceptacionTerminos extends Model
{
    /** @use HasFactory<\Database\Factories\AceptacionTerminosFactory> */
    use HasFactory, HasUuids;

    protected $table = 'aceptaciones_terminos';

    public $timestamps = false;

    protected $fillable = [
        'version_terminos_id',
        'usuario_auth_id',
        'aceptado_en',
        'ip',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'aceptado_en' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<VersionTerminos, $this>
     */
    public function versionTerminos(): BelongsTo
    {
        return $this->belongsTo(VersionTerminos::class, 'version_terminos_id');
    }
}

<?php

namespace App\Models;

// use Database\Factories\VersionTerminosFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="VersionTerminos",
 *     title="VersionTerminos",
 *     description="Versión de términos y condiciones",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="version", type="string"),
 *     @OA\Property(property="contenido_html", type="string"),
 *     @OA\Property(property="es_activo", type="boolean"),
 *     @OA\Property(property="publicado_en", type="string", format="date-time"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class VersionTerminos extends Model
{
    /** @use HasFactory<\Database\Factories\VersionTerminosFactory> */
    use HasFactory, HasUuids;

    protected $table = 'versiones_terminos';

    protected $fillable = [
        'version',
        'contenido_html',
        'es_activo',
        'publicado_en',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'es_activo' => 'boolean',
            'publicado_en' => 'datetime',
        ];
    }

    /**
     * @return HasMany<AceptacionTerminos, $this>
     */
    public function aceptaciones(): HasMany
    {
        return $this->hasMany(AceptacionTerminos::class, 'version_terminos_id');
    }
}

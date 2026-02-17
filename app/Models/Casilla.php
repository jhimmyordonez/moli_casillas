<?php

namespace App\Models;

use App\Enums\AccountStatus;
use App\Enums\DocType;
// use Database\Factories\CasillaFactory; // Factory needs rename too
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Casilla",
 *     title="Casilla",
 *     description="Cuenta de usuario de casilla",
 *
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="usuario_auth_id", type="string", format="uuid"),
 *     @OA\Property(property="tipo_documento", type="string", enum={"DNI", "CE", "RUC"}),
 *     @OA\Property(property="numero_documento", type="string"),
 *     @OA\Property(property="nombre_mostrar", type="string"),
 *     @OA\Property(property="email", type="string", format="email"),
 *     @OA\Property(property="estado", type="string", enum={"ACTIVO", "SUSPENDIDO"}),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Casilla extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\CasillaFactory> */
    use HasApiTokens, HasFactory, HasUuids;

    protected $table = 'casillas';

    protected $fillable = [
        'usuario_auth_id',
        'tipo_documento',
        'numero_documento',
        'nombre_mostrar',
        'email',
        'estado',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tipo_documento' => DocType::class,
            'estado' => AccountStatus::class,
        ];
    }

    /**
     * @return HasMany<Mensaje, $this>
     */
    public function mensajes(): HasMany
    {
        return $this->hasMany(Mensaje::class, 'casilla_id');
    }

    /**
     * @return HasMany<AceptacionTerminos, $this>
     */
    public function aceptacionesTerminos(): HasMany
    {
        return $this->hasMany(AceptacionTerminos::class, 'usuario_auth_id', 'usuario_auth_id');
    }

    public function haAceptadoTerminosVigentes(): bool
    {
        $terminosActivos = VersionTerminos::query()
            ->where('es_activo', true)
            ->latest('publicado_en')
            ->first();

        if (! $terminosActivos) {
            return true;
        }

        return $this->aceptacionesTerminos()
            ->where('version_terminos_id', $terminosActivos->id)
            ->exists();
    }
}

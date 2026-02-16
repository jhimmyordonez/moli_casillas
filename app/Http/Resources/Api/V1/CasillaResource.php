<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Casilla */
class CasillaResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'usuario_auth_id' => $this->usuario_auth_id,
            'tipo_documento' => $this->tipo_documento?->value,
            'numero_documento' => $this->numero_documento,
            'nombre_mostrar' => $this->nombre_mostrar,
            'email' => $this->email,
            'estado' => $this->estado?->value,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

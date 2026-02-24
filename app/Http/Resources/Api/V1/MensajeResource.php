<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Mensaje */
class MensajeResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'asunto' => $this->asunto,
            'documento' => $this->codigo_referencia,
            'hora' => $this->registrado_en?->format('h:i a'),
            'leido' => $this->leido_en !== null,
            'destacado' => (bool) $this->destacado,
            'seleccionado' => false,
            'contenido' => $this->cuerpo,
        ];
    }
}

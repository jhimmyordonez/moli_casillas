<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Adjunto */
class AdjuntoResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre_archivo' => $this->nombre_archivo,
            'tipo_mime' => $this->tipo_mime,
            'tamano_bytes' => $this->tamano_bytes,
            'subido_en' => $this->subido_en?->toIso8601String(),
        ];
    }
}

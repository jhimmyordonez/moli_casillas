<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\VersionTerminos */
class VersionTerminosResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'version' => $this->version,
            'contenido_html' => $this->contenido_html,
            'es_activo' => $this->es_activo,
            'publicado_en' => $this->publicado_en?->toIso8601String(),
        ];
    }
}

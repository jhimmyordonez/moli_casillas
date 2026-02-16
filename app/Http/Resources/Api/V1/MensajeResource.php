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
            'remitente_nombre' => $this->remitente_nombre,
            'remitente_entidad' => $this->remitente_entidad,
            'destinatario_nombre' => $this->destinatario_nombre,
            'asunto' => $this->asunto,
            'registrado_en' => $this->registrado_en?->toIso8601String(),
            'etiqueta_estado' => $this->etiqueta_estado_computada,
            'codigo_referencia' => $this->codigo_referencia,
            'acciones' => [
                'puede_ver' => true,
                'puede_descargar' => $this->adjuntos_count > 0 || $this->relationLoaded('adjuntos') && $this->adjuntos->isNotEmpty(),
                'puede_marcar_leido' => $this->leido_en === null,
                'puede_archivar' => $this->archivado_en === null,
            ],
        ];
    }
}

<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Mensaje */
class MensajeDetalleResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'remitente_nombre' => $this->remitente_nombre,
            'remitente_entidad' => $this->remitente_entidad,
            'destinatario_nombre' => $this->destinatario_nombre,
            'destinatario_tipo_doc' => $this->destinatario_tipo_doc,
            'destinatario_num_doc' => $this->destinatario_num_doc,
            'asunto' => $this->asunto,
            'cuerpo' => $this->cuerpo,
            'registrado_en' => $this->registrado_en?->toIso8601String(),
            'etiqueta_estado' => $this->etiqueta_estado_computada,
            'notificado_en' => $this->notificado_en?->toIso8601String(),
            'leido_en' => $this->leido_en?->toIso8601String(),
            'archivado_en' => $this->archivado_en?->toIso8601String(),
            'codigo_referencia' => $this->codigo_referencia,
            'codigo_expediente' => $this->codigo_expediente,
            'adjuntos' => AdjuntoResource::collection($this->whenLoaded('adjuntos')),
            'acciones' => [
                'puede_ver' => true,
                'puede_descargar' => $this->relationLoaded('adjuntos') && $this->adjuntos->isNotEmpty(),
                'puede_marcar_leido' => $this->leido_en === null,
                'puede_archivar' => $this->archivado_en === null,
            ],
        ];
    }
}

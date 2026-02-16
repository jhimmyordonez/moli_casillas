<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\EventType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AdjuntoResource;
use App\Models\Adjunto;
use App\Models\Casilla;
use App\Models\EventoMensaje;
use App\Models\Mensaje;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

use OpenApi\Annotations as OA;

class AdjuntoController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/casilla/messages/{mensajeId}/attachments",
     *     summary="Listar adjuntos de un mensaje",
     *     tags={"Adjuntos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="mensajeId", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Listado de adjuntos",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Adjunto"))
     *         )
     *     ),
     *     @OA\Response(response=404, description="Mensaje no encontrado")
     * )
     */
    public function index(Request $request, string $mensajeId): JsonResponse
    {
        /** @var \App\Models\Casilla $user */
        $user = $request->user();

        $mensaje = Mensaje::query()
            ->paraCasilla($user->id)
            ->where('id', $mensajeId)
            ->first();

        if (! $mensaje) {
            return response()->json([
                'data' => null,
                'meta' => null,
                'errors' => [
                    ['code' => 'NOT_FOUND', 'message' => 'Resource not found.'],
                ],
            ], 404);
        }

        $adjuntos = $mensaje->adjuntos;

        return response()->json([
            'data' => AdjuntoResource::collection($adjuntos),
            'meta' => null,
            'errors' => null,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/casilla/attachments/{id}/download",
     *     summary="Descargar adjunto",
     *     tags={"Adjuntos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Stream de descarga del archivo",
     *         @OA\MediaType(mediaType="application/octet-stream")
     *     ),
     *     @OA\Response(response=404, description="Adjunto no encontrado")
     * )
     */
    public function download(Request $request, string $id): StreamedResponse|JsonResponse
    {
        /** @var \App\Models\Casilla $user */
        $user = $request->user();

        $adjunto = Adjunto::query()
            ->with('mensaje')
            ->where('id', $id)
            ->first();

        if (! $adjunto || $adjunto->mensaje->casilla_id !== $user->id) {
            return response()->json([
                'data' => null,
                'meta' => null,
                'errors' => [
                    ['code' => 'NOT_FOUND', 'message' => 'Resource not found.'],
                ],
            ], 404);
        }

        EventoMensaje::query()->create([
            'mensaje_id' => $adjunto->mensaje_id,
            'tipo_evento' => EventType::Downloaded,
            'ocurrido_en' => now(),
            'actor_usuario_id' => $user->usuario_auth_id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if (Storage::disk($adjunto->driver_almacenamiento)->exists($adjunto->ruta_almacenamiento)) {
            return Storage::disk($adjunto->driver_almacenamiento)->download(
                $adjunto->ruta_almacenamiento,
                $adjunto->nombre_archivo,
                ['Content-Type' => $adjunto->tipo_mime]
            );
        }

        return response()->json([
            'data' => [
                'mensaje' => 'Archivo registrado para descarga (simulado).',
                'adjunto' => new AdjuntoResource($adjunto),
            ],
            'meta' => null,
            'errors' => null,
        ]);
    }
}

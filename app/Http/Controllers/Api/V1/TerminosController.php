<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AceptarTerminosRequest;
use App\Http\Resources\Api\V1\VersionTerminosResource;
use App\Models\AceptacionTerminos;
use App\Models\Casilla;
use App\Models\VersionTerminos;
use Illuminate\Http\JsonResponse;

use OpenApi\Annotations as OA;

class TerminosController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/terms/current",
     *     summary="Obtener términos y condiciones vigentes",
     *     tags={"Términos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Versión de términos vigente",
     *         @OA\JsonContent(ref="#/components/schemas/VersionTerminos")
     *     ),
     *     @OA\Response(response=404, description="No hay términos activos")
     * )
     */
    public function current(): JsonResponse
    {
        $terminos = VersionTerminos::query()
            ->where('es_activo', true)
            ->latest('publicado_en')
            ->first();

        if (! $terminos) {
            return response()->json([
                'data' => null,
                'meta' => null,
                'errors' => [
                    ['code' => 'NOT_FOUND', 'message' => 'No hay términos y condiciones vigentes.'],
                ],
            ], 404);
        }

        return response()->json([
            'data' => new VersionTerminosResource($terminos),
            'meta' => null,
            'errors' => null,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/terms/accept",
     *     summary="Aceptar términos y condiciones",
     *     tags={"Términos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"version_terminos_id"},
     *             @OA\Property(property="version_terminos_id", type="string", format="uuid")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Términos aceptados correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="mensaje", type="string", example="Términos aceptados correctamente."),
     *                 @OA\Property(property="aceptado_en", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function accept(AceptarTerminosRequest $request): JsonResponse
    {
        /** @var \App\Models\Casilla $user */
        $user = $request->user();

        $aceptacion = AceptacionTerminos::query()->updateOrCreate(
            [
                'version_terminos_id' => $request->validated('version_terminos_id'),
                'usuario_auth_id' => $user->usuario_auth_id,
            ],
            [
                'aceptado_en' => now(),
                'ip' => $request->ip(),
            ]
        );

        return response()->json([
            'data' => [
                'mensaje' => 'Términos aceptados correctamente.',
                'aceptado_en' => $aceptacion->aceptado_en->toIso8601String(),
            ],
            'meta' => null,
            'errors' => null,
        ]);
    }
}

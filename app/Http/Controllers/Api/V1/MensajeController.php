<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\EventType;
use App\Enums\MessageStatusCode;
use App\Enums\MessageStatusLabel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ListMensajesRequest;
use App\Http\Resources\Api\V1\MensajeDetalleResource;
use App\Http\Resources\Api\V1\MensajeResource;
use App\Models\Casilla;
use App\Models\EventoMensaje;
use App\Models\Mensaje;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use OpenApi\Annotations as OA;

class MensajeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/casilla/messages",
     *     summary="Listar mensajes",
     *     tags={"Mensajes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", description="Página", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", description="Items por página", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="asunto", in="query", description="Filtrar por asunto", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="fecha_desde", in="query", description="Filtrar por fecha desde (YYYY-MM-DD)", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="fecha_hasta", in="query", description="Filtrar por fecha hasta (YYYY-MM-DD)", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="etiqueta_estado", in="query", description="Filtrar por estado", required=false, @OA\Schema(type="string", enum={"SIN LEER", "NOTIFICADO", "LEÍDO", "ARCHIVADO"})),
     *     @OA\Response(
     *         response=200,
     *         description="Listado de mensajes",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Mensaje")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="total_pages", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function index(ListMensajesRequest $request): JsonResponse
    {
        /** @var \App\Models\Casilla $user */
        $user = $request->user();

        $query = Mensaje::query()
            ->paraCasilla($user->id)
            ->withCount('adjuntos')
            ->orderByDesc('registrado_en');

        if ($request->filled('asunto')) {
            $query->where('asunto', 'like', '%'.$request->input('asunto').'%');
        }

        if ($request->filled('fecha_desde')) {
            $query->where('registrado_en', '>=', $request->input('fecha_desde'));
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('registrado_en', '<=', $request->input('fecha_hasta').' 23:59:59');
        }

        if ($request->filled('etiqueta_estado')) {
            $query->conFiltroEstado($request->input('etiqueta_estado'));
        }

        // Remitente nombre
        if ($request->filled('remitente_nombre')) {
            $query->where('remitente_nombre', 'like', '%'.$request->input('remitente_nombre').'%');
        }

        // Destinatario doc number (si aplica filtro por destinatario)
        if ($request->filled('destinatario_num_doc')) {
            $query->where('destinatario_num_doc', $request->input('destinatario_num_doc'));
        }

        $perPage = $request->integer('per_page', 15);
        $paginator = $query->paginate($perPage);

        return response()->json([
            'data' => MensajeResource::collection($paginator->items()),
            'meta' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'total_pages' => $paginator->lastPage(),
            ],
            'errors' => null,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/casilla/messages/statuses",
     *     summary="Contar mensajes por estado",
     *     tags={"Mensajes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Conteo de estados",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="estados", type="array", @OA\Items(
     *                     @OA\Property(property="codigo", type="string"),
     *                     @OA\Property(property="etiqueta", type="string"),
     *                     @OA\Property(property="cantidad", type="integer")
     *                 )),
     *                 @OA\Property(property="cantidad_no_leidos", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function statuses(Request $request): JsonResponse
    {
        /** @var \App\Models\Casilla $user */
        $user = $request->user();

        $baseQuery = Mensaje::query()->paraCasilla($user->id);

        $unreadCount = (clone $baseQuery)
            ->whereNull('leido_en')
            ->whereNull('archivado_en')
            ->whereNull('notificado_en')
            ->count();

        $notifiedCount = (clone $baseQuery)
            ->whereNotNull('notificado_en')
            ->whereNull('leido_en')
            ->whereNull('archivado_en')
            ->count();

        $readCount = (clone $baseQuery)
            ->whereNotNull('leido_en')
            ->whereNull('archivado_en')
            ->count();

        $archivedCount = (clone $baseQuery)
            ->whereNotNull('archivado_en')
            ->count();

        return response()->json([
            'data' => [
                'estados' => [
                    [
                        'codigo' => 'UNREAD',
                        'etiqueta' => MessageStatusLabel::SinLeer->value,
                        'cantidad' => $unreadCount,
                    ],
                    [
                        'codigo' => 'NOTIFIED',
                        'etiqueta' => MessageStatusLabel::Notificado->value,
                        'cantidad' => $notifiedCount,
                    ],
                    [
                        'codigo' => 'READ',
                        'etiqueta' => MessageStatusLabel::Leido->value,
                        'cantidad' => $readCount,
                    ],
                    [
                        'codigo' => 'ARCHIVED',
                        'etiqueta' => MessageStatusLabel::Archivado->value,
                        'cantidad' => $archivedCount,
                    ],
                ],
                'cantidad_no_leidos' => $unreadCount + $notifiedCount,
            ],
            'meta' => null,
            'errors' => null,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/casilla/messages/{id}",
     *     summary="Obtener detalle del mensaje",
     *     tags={"Mensajes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Detalle del mensaje",
     *         @OA\JsonContent(ref="#/components/schemas/Mensaje")
     *     ),
     *     @OA\Response(response=404, description="Mensaje no encontrado")
     * )
     */
    public function show(Request $request, string $id): JsonResponse
    {
        /** @var \App\Models\Casilla $user */
        $user = $request->user();

        $mensaje = Mensaje::query()
            ->with('adjuntos')
            ->paraCasilla($user->id)
            ->where('id', $id)
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

        return response()->json([
            'data' => new MensajeDetalleResource($mensaje),
            'meta' => null,
            'errors' => null,
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/casilla/messages/{id}/read",
     *     summary="Marcar mensaje como leído",
     *     tags={"Mensajes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Mensaje marcado como leído",
     *         @OA\JsonContent(ref="#/components/schemas/Mensaje")
     *     ),
     *     @OA\Response(response=404, description="Mensaje no encontrado")
     * )
     */
    public function markRead(Request $request, string $id): JsonResponse
    {
        /** @var \App\Models\Casilla $user */
        $user = $request->user();

        $mensaje = Mensaje::query()
            ->paraCasilla($user->id)
            ->where('id', $id)
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

        if ($mensaje->leido_en === null) {
            $mensaje->update([
                'leido_en' => now(),
                'codigo_estado' => MessageStatusCode::Read,
                'etiqueta_estado' => MessageStatusLabel::Leido->value,
            ]);

            EventoMensaje::query()->create([
                'mensaje_id' => $mensaje->id,
                'tipo_evento' => EventType::Read,
                'ocurrido_en' => now(),
                'actor_usuario_id' => $user->usuario_auth_id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return response()->json([
            'data' => new MensajeDetalleResource($mensaje->load('adjuntos')),
            'meta' => null,
            'errors' => null,
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/casilla/messages/{id}/archive",
     *     summary="Archivar mensaje",
     *     tags={"Mensajes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Mensaje archivado",
     *         @OA\JsonContent(ref="#/components/schemas/Mensaje")
     *     ),
     *     @OA\Response(response=404, description="Mensaje no encontrado")
     * )
     */
    public function archive(Request $request, string $id): JsonResponse
    {
        /** @var \App\Models\Casilla $user */
        $user = $request->user();

        $mensaje = Mensaje::query()
            ->paraCasilla($user->id)
            ->where('id', $id)
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

        if ($mensaje->archivado_en === null) {
            $mensaje->update([
                'archivado_en' => now(),
                'codigo_estado' => MessageStatusCode::Archived,
                'etiqueta_estado' => MessageStatusLabel::Archivado->value,
            ]);

            EventoMensaje::query()->create([
                'mensaje_id' => $mensaje->id,
                'tipo_evento' => EventType::Archived,
                'ocurrido_en' => now(),
                'actor_usuario_id' => $user->usuario_auth_id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return response()->json([
            'data' => new MensajeDetalleResource($mensaje->load('adjuntos')),
            'meta' => null,
            'errors' => null,
        ]);
    }
}

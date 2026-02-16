<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Resources\Api\V1\CasillaResource;
use App\Models\Casilla;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use OpenApi\Annotations as OA;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     summary="Iniciar sesión con ID externo",
     *     tags={"Autenticación"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"usuario_auth_id", "tipo_documento", "numero_documento", "nombre_mostrar"},
     *             @OA\Property(property="usuario_auth_id", type="string", format="uuid", example="11111111-1111-1111-1111-111111111111"),
     *             @OA\Property(property="tipo_documento", type="string", enum={"DNI", "CE", "RUC"}, example="DNI"),
     *             @OA\Property(property="numero_documento", type="string", example="12345678"),
     *             @OA\Property(property="nombre_mostrar", type="string", example="Juan Pérez"),
     *             @OA\Property(property="email", type="string", example="juan@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inicio de sesión exitoso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string", example="1|AbCdEf..."),
     *                 @OA\Property(property="tipo_token", type="string", example="Bearer"),
     *                 @OA\Property(property="cuenta", ref="#/components/schemas/Casilla")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $casilla = Casilla::query()->updateOrCreate(
            ['usuario_auth_id' => $validated['usuario_auth_id']],
            [
                'tipo_documento' => $validated['tipo_documento'],
                'numero_documento' => $validated['numero_documento'],
                'nombre_mostrar' => $validated['nombre_mostrar'],
                'email' => $validated['email'] ?? null,
            ]
        );

        $token = $casilla->createToken('casilla-token')->plainTextToken;

        return response()->json([
            'data' => [
                'cuenta' => new CasillaResource($casilla),
                'token' => $token,
                'tipo_token' => 'Bearer',
            ],
            'meta' => null,
            'errors' => null,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/logout",
     *     summary="Cerrar sesión",
     *     tags={"Autenticación"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Sesión cerrada correctamente"),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        /** @var \App\Models\Casilla $user */
        $user = $request->user();

        if ($user) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'data' => ['mensaje' => 'Sesión cerrada correctamente.'],
            'meta' => null,
            'errors' => null,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/me",
     *     summary="Obtener usuario autenticado",
     *     tags={"Autenticación"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Datos del usuario actual",
     *         @OA\JsonContent(ref="#/components/schemas/Casilla")
     *     ),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new CasillaResource($request->user()),
            'meta' => null,
            'errors' => null,
        ]);
    }
}

<?php

namespace App\Http\Middleware;

use App\Models\Casilla;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTermsAccepted
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Casilla|null $user */
        $user = $request->user();

        if (! $user || ! $user->haAceptadoTerminosVigentes()) {
            return response()->json([
                'data' => null,
                'meta' => null,
                'errors' => [
                    [
                        'code' => 'TERMS_NOT_ACCEPTED',
                        'message' => 'Debe aceptar los términos y condiciones vigentes para acceder a este recurso.',
                    ],
                ],
            ], 403);
        }

        return $next($request);
    }
}

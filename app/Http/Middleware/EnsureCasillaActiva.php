<?php

namespace App\Http\Middleware;

use App\Enums\AccountStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCasillaActiva
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\Casilla $user */
        $user = $request->user();

        if ($user && $user->estado !== AccountStatus::Active) {
            return response()->json([
                'data' => null,
                'meta' => null,
                'errors' => [
                    [
                        'code' => 'ACCOUNT_SUSPENDED',
                        'message' => 'La cuenta de casilla se encuentra suspendida o inactiva.',
                    ],
                ],
            ], 403);
        }

        return $next($request);
    }
}

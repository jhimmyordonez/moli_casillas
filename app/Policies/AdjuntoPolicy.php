<?php

namespace App\Policies;

use App\Models\Adjunto;
use App\Models\Casilla;

class AdjuntoPolicy
{
    public function download(Casilla $user, Adjunto $adjunto): bool
    {
        return $adjunto->mensaje->casilla_id === $user->id;
    }
}

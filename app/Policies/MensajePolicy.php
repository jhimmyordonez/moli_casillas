<?php

namespace App\Policies;

use App\Models\Casilla;
use App\Models\Mensaje;

class MensajePolicy
{
    public function view(Casilla $user, Mensaje $mensaje): bool
    {
        return $mensaje->casilla_id === $user->id;
    }

    public function markRead(Casilla $user, Mensaje $mensaje): bool
    {
        return $mensaje->casilla_id === $user->id;
    }

    public function archive(Casilla $user, Mensaje $mensaje): bool
    {
        return $mensaje->casilla_id === $user->id;
    }
}

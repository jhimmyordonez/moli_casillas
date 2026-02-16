<?php

namespace App\Policies;

use App\Models\CasillaAccount;
use App\Models\Message;

class MessagePolicy
{
    public function view(CasillaAccount $user, Message $message): bool
    {
        return $message->casilla_id === $user->id;
    }

    public function markRead(CasillaAccount $user, Message $message): bool
    {
        return $message->casilla_id === $user->id;
    }

    public function archive(CasillaAccount $user, Message $message): bool
    {
        return $message->casilla_id === $user->id;
    }
}

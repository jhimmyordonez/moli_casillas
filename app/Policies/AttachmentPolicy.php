<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\CasillaAccount;

class AttachmentPolicy
{
    public function download(CasillaAccount $user, Attachment $attachment): bool
    {
        return $attachment->message->casilla_id === $user->id;
    }
}

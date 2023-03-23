<?php

namespace App\Policies\Api\V2;

use App\Models\InternalNotification;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InternalNotificationPolicy
{
    use HandlesAuthorization;

    public function read(User $user, InternalNotification $internalNotification)
    {
        return $internalNotification->isOwner($user);
    }
}

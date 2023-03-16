<?php

namespace App\Nova\Observers;

use App\Models\User;

class UserObserver
{
    public function updating(User $user)
    {
        if ($user->isDirty('verified_at') && $user->verified_at && is_null($user->hat_requested)) {
            $user->hat_requested = true;
        }
    }
}

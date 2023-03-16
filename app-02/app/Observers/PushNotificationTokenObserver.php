<?php

namespace App\Observers;

use App\Models\PushNotificationToken;
use Illuminate\Support\Str;

class PushNotificationTokenObserver
{
    public function creating(PushNotificationToken $pushNotificationToken): void
    {
        $pushNotificationToken->uuid = Str::uuid();
    }
}

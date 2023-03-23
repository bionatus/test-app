<?php

namespace App\Observers;

use App\Models\InternalNotification;
use App\Notifications\UnreadNotificationCountUpdatedNotification;
use Str;

class InternalNotificationObserver
{
    public function creating(InternalNotification $internalNotification): void
    {
        $internalNotification->uuid = Str::uuid();
    }

    public function created(InternalNotification $internalNotification): void
    {
        $user                     = $internalNotification->user()->first();
        $unreadNotificationsCount = $user->getUnreadNotificationsCount();

        $user->notify(new UnreadNotificationCountUpdatedNotification($unreadNotificationsCount));
    }
}

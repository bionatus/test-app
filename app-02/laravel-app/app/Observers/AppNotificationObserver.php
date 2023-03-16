<?php

namespace App\Observers;

use App\AppNotification;
use App\Models\User;
use App\Notifications\UnreadNotificationCountUpdatedNotification;

class AppNotificationObserver
{
    public function created(AppNotification $appNotification): void
    {
        if (!($user = User::find($appNotification->user_id))) {
            return;
        }

        $unreadNotificationsCount = $user->getUnreadNotificationsCount();

        $user->notify(new UnreadNotificationCountUpdatedNotification($unreadNotificationsCount));
    }
}

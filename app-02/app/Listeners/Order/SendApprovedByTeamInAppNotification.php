<?php

namespace App\Listeners\Order;

use App\Events\Order\ApprovedByTeam as ApprovedByTeamEvent;
use App\Notifications\User\ApprovedByTeamInAppNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendApprovedByTeamInAppNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ApprovedByTeamEvent $event)
    {
        $order = $event->order();
        $user  = $order->user;

        if ($user) {
            $user->notify(new ApprovedByTeamInAppNotification($order));
        }
    }
}

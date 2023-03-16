<?php

namespace App\Listeners\Order;

use App\Events\Order\ApprovedByTeam as ApprovedByTeamEvent;
use App\Notifications\User\ApprovedByTeamSmsNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendApprovedByTeamSmsNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ApprovedByTeamEvent $event)
    {
        $order = $event->order();
        $user  = $order->user;

        if ($user) {
            $user->notify(new ApprovedByTeamSmsNotification($order));
        }
    }
}

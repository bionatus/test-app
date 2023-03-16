<?php

namespace App\Listeners\Order;

use App\Events\Order\Assigned as AssignedEvent;
use App\Notifications\User\AssignSmsNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendAssignSmsNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(AssignedEvent $event)
    {
        $order = $event->order();
        $user  = $order->user;

        if ($user) {
            $user->notify(new AssignSmsNotification($order));
        }
    }
}

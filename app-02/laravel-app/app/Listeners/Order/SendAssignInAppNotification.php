<?php

namespace App\Listeners\Order;

use App\Events\Order\Assigned;
use App\Notifications\User\AssignInAppNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendAssignInAppNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(Assigned $event)
    {
        $order = $event->order();
        $user  = $order->user;

        if ($user) {
            $user->notify(new AssignInAppNotification($order));
        }
    }
}

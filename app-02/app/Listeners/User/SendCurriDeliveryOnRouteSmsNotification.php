<?php

namespace App\Listeners\User;

use App;
use App\Events\Order\Delivery\Curri\OnRoute;
use App\Notifications\User\CurriDeliveryOnRouteSmsNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendCurriDeliveryOnRouteSmsNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OnRoute $event)
    {
        $curriDelivery = $event->curriDelivery();
        $order         = $curriDelivery->orderDelivery->order;
        $user          = $order->user;

        if ($user) {
            $user->notify(new CurriDeliveryOnRouteSmsNotification($order));
        }
    }
}

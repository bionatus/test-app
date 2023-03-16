<?php

namespace App\Listeners\User;

use App;
use App\Events\Order\Delivery\Curri\UserConfirmationRequired as UserConfirmationRequiredEvent;
use App\Notifications\User\CurriDeliveryConfirmationRequiredSmsNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendCurriDeliveryConfirmationRequiredSmsNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(UserConfirmationRequiredEvent $event)
    {
        $order = $event->order();
        $user  = $order->user;

        if ($user) {
            $user->notify(new CurriDeliveryConfirmationRequiredSmsNotification($order));
        }
    }
}

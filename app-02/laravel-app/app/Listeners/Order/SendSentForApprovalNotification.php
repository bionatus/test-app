<?php

namespace App\Listeners\Order;

use App\Events\Order\SentForApproval;
use App\Notifications\User\OrderSentForApprovalNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSentForApprovalNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(SentForApproval $event)
    {
        $order = $event->order();
        $user  = $order->user;

        if ($user) {
            $user->notify(new OrderSentForApprovalNotification($order));
        }
    }
}

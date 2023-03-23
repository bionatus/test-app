<?php

namespace App\Listeners\User;

use App;
use App\Events\Order\OrderEventInterface;
use App\Jobs\User\UpdatePendingApprovalOrdersCounter as UpdatePendingApprovalOrdersCounterJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdatePendingApprovalOrdersCounter implements ShouldQueue
{
    public function handle(OrderEventInterface $event)
    {
        $order = $event->order();
        $user  = $order->user;

        if ($user) {
            UpdatePendingApprovalOrdersCounterJob::dispatch($user);
        }
    }
}

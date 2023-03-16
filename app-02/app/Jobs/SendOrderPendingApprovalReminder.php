<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Order\Scopes\ByInPendingApprovalInLastWeek;
use App\Models\Order\Scopes\ByUserNotNull;
use App\Models\Order\Scopes\ByUserTimezone;
use App\Notifications\User\OrderPendingApprovalInAppNotification;
use App\Notifications\User\OrderPendingApprovalSmsLinkNotification;
use App\Notifications\User\OrderPendingApprovalSmsNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendOrderPendingApprovalReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private ?string $timezone;

    public function __construct(?string $timezone)
    {
        $this->timezone = $timezone;
        $this->onConnection('database');
    }

    public function handle()
    {
        $orders = Order::scoped(new ByInPendingApprovalInLastWeek())
            ->scoped(new ByUserTimezone($this->timezone))
            ->scoped(new ByUserNotNull())
            ->cursor();

        $orders->each(function(Order $order) {
            $user = $order->user;

            $user->notify(new OrderPendingApprovalInAppNotification($order));
            $user->notify(new OrderPendingApprovalSmsNotification($order));
            $user->notify(new OrderPendingApprovalSmsLinkNotification($order));
        });
    }
}

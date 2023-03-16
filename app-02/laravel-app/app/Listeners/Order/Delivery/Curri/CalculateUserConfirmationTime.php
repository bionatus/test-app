<?php

namespace App\Listeners\Order\Delivery\Curri;

use App;
use App\Actions\Models\Order\Delivery\Curri\LegacyCalculateJobExecutionTime;
use App\Events\Order\OrderEventInterface;
use App\Jobs\Order\Delivery\Curri\DispatchUserConfirmationRequired as DispatchUserConfirmationRequiredJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class CalculateUserConfirmationTime implements ShouldQueue
{
    public function handle(OrderEventInterface $event)
    {
        $order         = $event->order();
        $timezone      = $order->supplier->timezone;
        $orderDelivery = $order->orderDelivery;

        if ($orderDelivery->isCurriDelivery() && $order->isApproved()) {
            $date         = $orderDelivery->date->format('Y-m-d');
            $time         = Str::of($orderDelivery->start_time->format('H:i'));
            $deliveryDate = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $time, $timezone)->startOfHour();

            $delayTime = App::make(LegacyCalculateJobExecutionTime::class, ['order' => $order])->execute();

            DispatchUserConfirmationRequiredJob::dispatch($order, $deliveryDate)->delay($delayTime);
        }
    }
}

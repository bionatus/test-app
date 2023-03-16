<?php

namespace App\Actions\Models\Order\Delivery;

use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class CalculateJobExecutionTime
{
    private Order $order;
    private int   $minutes;

    public function __construct(Order $order, int $minutes = 30)
    {
        $this->order   = $order;
        $this->minutes = $minutes;
    }

    public function execute(): Carbon
    {
        $order         = $this->order;
        $orderDelivery = $order->orderDelivery;

        $date = $orderDelivery->date->format('Y-m-d');
        $time = Str::of($orderDelivery->start_time->format('H:i'));

        $now          = Carbon::now();
        $deliveryDate = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $time);

        $diff      = $now->diffInMinutes($deliveryDate, false);
        $delayDate = $now->addMicroseconds(1000);
        if ($diff >= $this->minutes) {
            $delayDate = $deliveryDate->subMinutes($this->minutes);
        }

        return $delayDate;
    }
}

<?php

namespace App\Jobs\Order\Delivery\Curri;

use App;
use App\Events\Order\Delivery\Curri\UserConfirmationRequired;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class DispatchUserConfirmationRequired implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Order  $order;
    private Carbon $deliveryDate;

    public function __construct(Order $order, Carbon $deliveryDate)
    {
        $this->onConnection('database');

        $this->order        = $order;
        $this->deliveryDate = $deliveryDate;
    }

    public function uniqueId(): string
    {
        $orderDelivery = $this->order->orderDelivery;
        $date          = $orderDelivery->date->format('Ymd');
        $start         = $orderDelivery->start_time->format('His');
        $end           = $orderDelivery->end_time->format('His');

        return $this->order->getKey() . $date . $start . $end;
    }

    public function uniqueVia(): Repository
    {
        return Cache::driver('database');
    }

    public function handle()
    {
        $timezone      = $this->order->supplier->timezone;
        $orderDelivery = $this->order->orderDelivery;

        $date = $orderDelivery->date->format('Y-m-d');
        $time = Str::of($orderDelivery->start_time->format('H:i'));

        $currentDeliveryDate = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $time, $timezone)->startOfHour();

        if ($this->deliveryDate == $currentDeliveryDate && $this->order->isApproved()) {
            UserConfirmationRequired::dispatch($this->order);
        }
    }
}

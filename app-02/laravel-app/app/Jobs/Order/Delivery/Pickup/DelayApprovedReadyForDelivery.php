<?php

namespace App\Jobs\Order\Delivery\Pickup;

use App;
use App\Actions\Models\Order\ChangeStatus;
use App\Models\Order;
use App\Models\Substatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DelayApprovedReadyForDelivery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->onConnection('database');
    }

    public function handle()
    {
        if ($this->order->lastSubStatusIsAnyOf([Substatus::STATUS_APPROVED_AWAITING_DELIVERY])) {
            App::make(ChangeStatus::class, [
                'order'       => $this->order,
                'substatusId' => Substatus::STATUS_APPROVED_READY_FOR_DELIVERY,
            ])->execute();
        }
    }
}

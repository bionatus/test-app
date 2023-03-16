<?php

namespace App\Jobs\Order;

use App;
use App\Actions\Models\Order\ChangeStatus;
use App\Events\Order\LegacyCompleted;
use App\Models\Order;
use App\Models\Substatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CompleteApproved implements ShouldQueue
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
        $order = $this->order;

        if ($order->isAssigned() && $order->isApproved()) {
            $order = App::make(ChangeStatus::class,
                ['order' => $order, 'substatusId' => Substatus::STATUS_COMPLETED_DONE])->execute();

            LegacyCompleted::dispatch($order);
        }
    }
}

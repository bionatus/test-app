<?php

namespace App\Jobs\Order;

use App;
use App\Actions\Models\Order\ChangeStatus;
use App\Events\Order\Completed;
use App\Models\Order;
use App\Models\Substatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DelayComplete implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Order $order;

    public function __construct(Order $order)
    {
        $this->onConnection('database');

        $this->order = $order;
    }

    public function handle()
    {
        if ($this->order->fresh()->lastStatus->substatus_id === Substatus::STATUS_APPROVED_DELIVERED) {
            $order = App::make(ChangeStatus::class,
                ['order' => $this->order, 'substatusId' => Substatus::STATUS_COMPLETED_DONE])->execute();

            Completed::dispatch($order);
        }
    }
}

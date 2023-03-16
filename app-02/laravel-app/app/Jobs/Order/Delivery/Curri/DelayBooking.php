<?php

namespace App\Jobs\Order\Delivery\Curri;

use App;
use App\Actions\Models\Order\ChangeStatus;
use App\Actions\Models\Order\Delivery\Curri\Book;
use App\Events\Order\Delivery\Curri\Booked;
use App\Models\Order;
use App\Models\Substatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DelayBooking implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Order $order;

    public function __construct(Order $order)
    {
        $this->onConnection('database');

        $this->order = $order;
    }

    /**
     * @throws \Throwable
     */
    public function handle()
    {
        if ($this->order->lastSubStatusIsAnyOf([Substatus::STATUS_APPROVED_AWAITING_DELIVERY])) {
            App::make(Book::class, ['order' => $this->order])->execute();
            App::make(ChangeStatus::class, [
                'order'       => $this->order,
                'substatusId' => Substatus::STATUS_APPROVED_READY_FOR_DELIVERY,
            ])->execute();
            Booked::dispatch($this->order);
        }
    }
}

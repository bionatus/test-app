<?php

namespace App\Jobs\Order\Delivery\Curri;

use App;
use App\Actions\Models\Order\Delivery\Curri\LegacyBook;
use App\Events\Order\Delivery\Curri\Booked;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LegacyDelayBooking implements ShouldQueue
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
        if ($this->order->isApproved()) {
            App::make(LegacyBook::class, ['order' => $this->order])->execute();
            Booked::dispatch($this->order);
        }
    }
}

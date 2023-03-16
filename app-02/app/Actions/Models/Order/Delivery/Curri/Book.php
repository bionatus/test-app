<?php

namespace App\Actions\Models\Order\Delivery\Curri;

use App;
use App\Models\CurriDelivery;
use App\Models\Order;
use App\Services\Curri\Curri;
use App\Types\Money;
use Illuminate\Support\Carbon;

class Book
{
    private Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @throws \App\Exceptions\CurriException
     */
    public function execute(): void
    {
        $curri    = App::make(Curri::class);
        $delivery = $this->order->orderDelivery;
        /** @var CurriDelivery $curriDelivery */
        $curriDelivery = $delivery->deliverable;

        $result = $curri->bookDelivery($delivery);

        $curriDelivery->book_id           = $result['id'];
        $curriDelivery->tracking_id       = $result['tracking_id'];
        $curriDelivery->user_confirmed_at = Carbon::now();
        $curriDelivery->save();

        $delivery->fee = Money::toDollars($result['price']);
        $delivery->save();
    }
}

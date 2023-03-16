<?php

namespace App\Actions\Models\Order\Delivery\Curri;

use App;
use App\Models\CurriDelivery;
use App\Models\Order;
use App\Models\OrderSubstatus;
use App\Models\Substatus;
use App\Services\Curri\Curri;
use App\Types\Money;
use Illuminate\Support\Carbon;

class LegacyBook
{
    private Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @throws \Throwable
     */
    public function execute(): Order
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

        $orderSubStatus               = App::make(OrderSubstatus::class);
        $orderSubStatus->order_id     = $this->order->getKey();
        $orderSubStatus->substatus_id = Substatus::STATUS_COMPLETED_DONE;
        $orderSubStatus->save();

        return $this->order;
    }
}

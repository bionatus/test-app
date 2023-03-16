<?php

namespace App\Handlers\OrderSubstatus;

use App;
use App\Actions\Models\Order\Delivery\CalculateJobExecutionTime;
use App\Actions\Models\Order\Delivery\Curri\Book;
use App\Jobs\Order\Delivery\Curri\DelayBooking;
use App\Models\Order;
use App\Models\Substatus;
use Illuminate\Support\Carbon;

class OrderSubstatusCurriHandler extends BaseOrderSubstatusHandler implements OrderSubstatusUpdated
{
    /**
     * @throws \Throwable
     */
    public function processPendingApprovalQuoteNeeded(Order $order): Order
    {
        $orderDelivery = $order->orderDelivery;
        $substatusId   = Substatus::STATUS_APPROVED_AWAITING_DELIVERY;
        if ($orderDelivery->isNeededLater() && $this->diffMinutes($orderDelivery) > self::LIMIT_MINUTES) {
            $delayTime = App::make(CalculateJobExecutionTime::class, ['order' => $order])->execute();
            DelayBooking::dispatch($order)->delay($delayTime);
        } else {
            $order = $this->checkDeliveryIsOutdated($order);
            App::make(Book::class, ['order' => $order])->execute();
            $substatusId = Substatus::STATUS_APPROVED_READY_FOR_DELIVERY;
        }

        return $this->changeSubstatus($order, $substatusId);
    }

    private function checkDeliveryIsOutdated(Order $order): Order
    {
        $orderDelivery    = $order->orderDelivery;
        $fullDate         = $orderDelivery->date->format('Y-m-d') . ' ' . $orderDelivery->start_time->format('H:i');
        $deliveryDateTime = Carbon::createFromFormat('Y-m-d H:i', $fullDate);
        if ($deliveryDateTime->isPast()) {
            $order->orderDelivery->date       = Carbon::now()->format('Y-m-d');
            $order->orderDelivery->start_time = Carbon::now()->format('H:i');
            $order->orderDelivery->end_time   = Carbon::now()->addHour()->format('H:i');
            $order->orderDelivery->save();
        }

        return $order;
    }
}

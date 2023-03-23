<?php

namespace App\Handlers\OrderSubstatus;

use App;
use App\Actions\Models\Order\Delivery\CalculateJobExecutionTime;
use App\Jobs\Order\Delivery\Pickup\DelayApprovedReadyForDelivery;
use App\Models\Order;
use App\Models\Substatus;

class OrderSubstatusPickupHandler extends BaseOrderSubstatusHandler implements OrderSubstatusUpdated
{
    public function processPendingApprovalQuoteNeeded(Order $order): Order
    {
        $orderDelivery = $order->orderDelivery;
        $substatusId   = Substatus::STATUS_APPROVED_READY_FOR_DELIVERY;

        if ($orderDelivery->isNeededLater() && $this->diffMinutes($orderDelivery) > self::LIMIT_MINUTES) {
            $delayTime = App::make(CalculateJobExecutionTime::class, ['order' => $order])->execute();
            DelayApprovedReadyForDelivery::dispatch($order)->delay($delayTime);
            $substatusId = Substatus::STATUS_APPROVED_AWAITING_DELIVERY;
        }

        return $this->changeSubstatus($order, $substatusId);
    }
}

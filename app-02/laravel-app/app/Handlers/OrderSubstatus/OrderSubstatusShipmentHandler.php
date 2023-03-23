<?php

namespace App\Handlers\OrderSubstatus;

use App;
use App\Models\Order;
use App\Models\Substatus;

class OrderSubstatusShipmentHandler extends BaseOrderSubstatusHandler implements OrderSubstatusUpdated
{
    public function processPendingApprovalQuoteNeeded(Order $order): Order
    {
        return $this->changeSubstatus($order, Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED);
    }
}

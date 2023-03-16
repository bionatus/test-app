<?php

namespace App\Handlers\OrderSubstatus;

use App\Models\Order;

interface OrderSubstatusUpdated
{
    public function processPendingApprovalQuoteNeeded(Order $order);
}

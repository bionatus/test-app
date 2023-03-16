<?php

namespace App\Actions\Models\Order;

use App;
use App\Models\Order;
use App\Models\OrderSubstatus;

class ChangeStatus
{
    private ?string $detail;
    private Order   $order;
    private int     $substatusId;

    public function __construct(Order $order, int $substatusId, string $detail = null)
    {
        $this->detail      = $detail;
        $this->order       = $order;
        $this->substatusId = $substatusId;
    }

    public function execute(): Order
    {
        $orderSubStatus               = new OrderSubstatus();
        $orderSubStatus->order_id     = $this->order->getKey();
        $orderSubStatus->detail       = $this->detail;
        $orderSubStatus->substatus_id = $this->substatusId;
        $orderSubStatus->save();

        return $this->order->fresh();
    }
}

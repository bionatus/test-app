<?php

namespace App\Actions\Models\PubnubChannel;

use App\Models\Order;

class GetChannelByOrder
{
    private Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function execute(): string
    {
        if (is_null($this->order->user)) {
            return $this->order->orderLockedData->channel;
        }

        $pubnubChannel = (new GetPubnubChannel($this->order->supplier, $this->order->user))->execute();

        return $pubnubChannel->getRouteKey();
    }
}

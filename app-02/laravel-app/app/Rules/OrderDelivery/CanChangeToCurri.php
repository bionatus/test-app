<?php

namespace App\Rules\OrderDelivery;

use App\Models\CurriDelivery;
use App\Models\WarehouseDelivery;
use App\Models\OtherDelivery;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Pickup;
use App\Models\ShipmentDelivery;
use Illuminate\Contracts\Validation\Rule;

class CanChangeToCurri implements Rule
{
    private Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function passes($attribute, $value): bool
    {
        if ($value !== OrderDelivery::TYPE_CURRI_DELIVERY) {
            return true;
        }

        /** @var CurriDelivery|Pickup|OtherDelivery|ShipmentDelivery|WarehouseDelivery $deliverable */
        $deliverable = $this->order->orderDelivery->deliverable;

        if (!$deliverable->hasDestinationAddress()) {
            return false;
        }

        return true;
    }

    public function message(): string
    {
        return 'It cannot change to Curri because destination address does not exist.';
    }
}

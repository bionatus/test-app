<?php

namespace App\Policies\LiveApi\V2;

use App\Constants\MediaCollectionNames;
use App\Models\Order;
use App\Models\Staff;
use App\Models\Substatus;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    public function assign(Staff $staff, Order $order): bool
    {
        return $order->isProcessor($staff) && $order->isPending();
    }

    public function cancel(Staff $staff, Order $order): bool
    {
        return $order->isProcessor($staff) && $order->isPending();
    }

    public function cancelInProgress(Staff $staff, Order $order): bool
    {
        $isShipmentQuoteUpdated = $order->orderDelivery->isShipmentDelivery() && $order->lastSubStatusIsAnyOf([Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED]);

        return $order->isProcessor($staff) && ($order->lastSubStatusIsAnyOf([
                    Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED,
                    Substatus::STATUS_APPROVED_AWAITING_DELIVERY,
                ]) || $isShipmentQuoteUpdated);
    }

    public function read(Staff $staff, Order $order): bool
    {
        return $order->isProcessor($staff);
    }

    public function update(Staff $staff, Order $order): bool
    {
        $hasInvoice = !!$order->getFirstMedia(MediaCollectionNames::INVOICE);
        if (!$order->lastSubStatusIsAnyOf([Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED])) {
            $hasInvoice = true;
        }

        $delivery    = $order->orderDelivery;
        $updateCurri = $delivery->isCurriDelivery() && $order->lastSubStatusIsAnyOf([
                Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED,
                Substatus::STATUS_APPROVED_AWAITING_DELIVERY,
                Substatus::STATUS_APPROVED_READY_FOR_DELIVERY,
                Substatus::STATUS_APPROVED_DELIVERED,
            ]);

        $updatePickup = $delivery->isPickup() && $order->lastSubStatusIsAnyOf([
                Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED,
                Substatus::STATUS_APPROVED_AWAITING_DELIVERY,
                Substatus::STATUS_APPROVED_READY_FOR_DELIVERY,
            ]);

        $updateShipment = $delivery->isShipmentDelivery() && $order->lastSubStatusIsAnyOf([
                Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED,
                Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED,
            ]);

        return $order->isProcessor($staff) && $hasInvoice && ($updateCurri || $updatePickup || $updateShipment);
    }

    public function updateItems(Staff $staff, Order $order): bool
    {
        return $order->isProcessor($staff) && $order->isPending();
    }

    public function legacyUpdate(Staff $staff, Order $order): bool
    {
        return $order->isProcessor($staff) && $order->isPending();
    }

    public function sendForApproval(Staff $staff, Order $order): bool
    {
        return $order->isProcessor($staff) && $order->doesntHavePendingItems() && $order->lastSubStatusIsAnyOf([Substatus::STATUS_PENDING_ASSIGNED]);
    }

    public function updateExtraItemsInProgress(Staff $staff, Order $order): bool
    {
        return $order->isProcessor($staff) && $order->lastSubStatusIsAnyOf([
                Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED,
                Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED,
                Substatus::STATUS_APPROVED_AWAITING_DELIVERY,
                Substatus::STATUS_APPROVED_READY_FOR_DELIVERY,
                Substatus::STATUS_APPROVED_DELIVERED,
            ]);
    }

    public function complete(Staff $staff, Order $order): bool
    {
        if ($order->orderDelivery->isCurriDelivery()) {
            return false;
        }
        $pickupFlag = $order->orderDelivery->isPickup() && $order->lastSubStatusIsAnyOf([
                Substatus::STATUS_APPROVED_READY_FOR_DELIVERY,
                Substatus::STATUS_APPROVED_DELIVERED,
            ]);

        $shipmentFlag = $order->orderDelivery->isShipmentDelivery() && $order->lastSubStatusIsAnyOf([
                Substatus::STATUS_APPROVED_AWAITING_DELIVERY,
            ]);

        return $order->isProcessor($staff) && ($pickupFlag || $shipmentFlag);
    }
}

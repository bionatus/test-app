<?php

namespace App\Policies\Api\V4;

use App\Models\Order;
use App\Models\Substatus;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    public function read(User $user, Order $order): bool
    {
        return $order->isOwner($user);
    }

    public function cancel(User $user, Order $order): bool
    {
        if (!$order->isOwner($user)) {
            return false;
        }
        if (!$order->orderDelivery) {
            return $order->isPending() || $order->isPendingApproval();
        }

        if ($order->isPending() || ($order->lastStatus->substatus_id == Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED)) { // no matter the type
            return true;
        }
        if ($order->orderDelivery->isPickup()) {
            return $order->lastStatus->substatus_id == Substatus::STATUS_APPROVED_DELIVERED;
        }
        if ($order->orderDelivery->isCurriDelivery()) {
            return $order->lastStatus->substatus_id == Substatus::STATUS_APPROVED_AWAITING_DELIVERY;
        }

        return false;
    }

    public function complete(User $user, Order $order): bool
    {
        $delivery = $order->orderDelivery;

        return $order->isOwner($user) && $delivery->isCurriDelivery() && $order->lastStatus->substatus_id === Substatus::STATUS_APPROVED_DELIVERED;
    }

    public function createDelivery(User $user, Order $order): bool
    {
        return $order->isOwner($user) && ($order->lastSubStatusIsAnyOf([
                Substatus::STATUS_PENDING_APPROVAL_FULFILLED,
            ]));
    }

    public function approve(User $user, Order $order): bool
    {
        return $order->isOwner($user) && $order->lastSubStatusIsAnyOf([
                Substatus::STATUS_PENDING_APPROVAL_FULFILLED,
                Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED,
            ]);
    }

    public function confirmPickup(User $user, Order $order): bool
    {
        return $order->isOwner($user) && $order->orderDelivery->isPickup() && $order->isApproved() && $order->lastStatus->substatus_id !== Substatus::STATUS_APPROVED_DELIVERED;
    }

    public function approveShipment(User $user, Order $order): bool
    {
        return $order->isOwner($user) && $order->orderDelivery->isShipmentDelivery() && $order->lastStatus->substatus_id === Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED;
    }

    public function updateItemOrder(User $user, Order $order): bool
    {
        return $order->isOwner($user) && $order->lastStatus->substatus_id === Substatus::STATUS_PENDING_APPROVAL_FULFILLED;
    }

    public function updateName(User $user, Order $order): bool
    {
        $delivery           = $order->orderDelivery;
        $validCurryDelivery = $delivery->isCurriDelivery() && $order->lastSubStatusIsAnyOf([
                Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED,
                Substatus::STATUS_APPROVED_AWAITING_DELIVERY,
                Substatus::STATUS_APPROVED_READY_FOR_DELIVERY,
                Substatus::STATUS_APPROVED_DELIVERED,
                Substatus::STATUS_COMPLETED_DONE,
            ]);

        $validPickupDelivery = $delivery->isPickup() && $order->lastSubStatusIsAnyOf([
                Substatus::STATUS_APPROVED_AWAITING_DELIVERY,
                Substatus::STATUS_APPROVED_READY_FOR_DELIVERY,
                Substatus::STATUS_APPROVED_DELIVERED,
                Substatus::STATUS_COMPLETED_DONE,
            ]);

        $validShipmentDelivery = $delivery->isShipmentDelivery() && $order->lastSubStatusIsAnyOf([
                Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED,
                Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED,
                Substatus::STATUS_APPROVED_AWAITING_DELIVERY,
                Substatus::STATUS_COMPLETED_DONE,
            ]);

        return $order->isOwner($user) && ($validCurryDelivery || $validPickupDelivery || $validShipmentDelivery);
    }

    public function confirmTotal(User $user, Order $order): bool
    {
        return $order->isOwner($user) && $order->orderDelivery->isPickup() && $order->lastStatus->substatus_id === Substatus::STATUS_APPROVED_READY_FOR_DELIVERY;
    }
}

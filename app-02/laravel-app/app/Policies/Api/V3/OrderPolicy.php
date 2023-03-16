<?php

namespace App\Policies\Api\V3;

use App\Models\CurriDelivery;
use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    public function read(User $user, Order $order): bool
    {
        return $order->isOwner($user);
    }

    public function approve(User $user, Order $order): bool
    {
        return $order->isOwner($user) && $order->isPendingApproval();
    }

    public function cancel(User $user, Order $order): bool
    {
        return $order->isOwner($user) && ($order->isPending() || $order->isPendingApproval());
    }

    public function confirmCurriOrder(User $user, Order $order): bool
    {
        $orderDelivery = $order->orderDelivery;
        /** @var CurriDelivery $curriDelivery */
        $curriDelivery = $orderDelivery->deliverable;

        return $order->isOwner($user) && $order->isApproved() && $orderDelivery->isCurriDelivery() && !$curriDelivery->isConfirmedByUser();
    }

    public function share(User $user, Order $order): bool
    {
        return $order->isOwner($user);
    }

    public function updateDelivery(User $user, Order $order): bool
    {
        return $order->isOwner($user) && ($order->isPending() || $order->isPendingApproval());
    }
}

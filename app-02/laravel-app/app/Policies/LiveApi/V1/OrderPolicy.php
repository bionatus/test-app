<?php

namespace App\Policies\LiveApi\V1;

use App\Models\CurriDelivery;
use App\Models\Order;
use App\Models\Staff;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Carbon;

class OrderPolicy
{
    use HandlesAuthorization;

    public function approveUnauthenticated(?Staff $staff, Order $order): bool
    {
        return $order->isPendingApproval();
    }

    public function assign(Staff $staff, Order $order): bool
    {
        return $order->isProcessor($staff) && $order->isPending();
    }

    public function cancel(Staff $staff, Order $order): bool
    {
        return $order->isProcessor($staff) && ($order->isPending() || $order->isPendingApproval());
    }

    public function cancelInProgress(Staff $staff, Order $order): bool
    {
        return $order->isProcessor($staff) && ($order->isApproved() || $order->isCompleted());
    }

    public function updateInProgressDelivery(Staff $staff, Order $order): bool
    {
        return $order->isProcessor($staff) && $order->isApproved() && $this->curriConfirmationNotShownToUser($order);
    }

    private function curriConfirmationNotShownToUser(Order $order): bool
    {
        $orderDelivery = $order->orderDelivery;
        $canUpdate     = true;

        if ($orderDelivery->isCurriDelivery()) {
            $timezone = $order->supplier->timezone;
            $date     = $orderDelivery->date->format('Y-m-d');
            $time     = $orderDelivery->start_time->format('H:i');

            $deliveryDate = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $time, $timezone);
            $diff         = Carbon::now()->diffInMinutes($deliveryDate, false);
            if ($diff <= 30) {
                $canUpdate = false;
            }
        }

        return $canUpdate;
    }

    public function complete(Staff $staff, Order $order): bool
    {
        return $order->isProcessor($staff) && $order->isAssigned() && $order->isApproved();
    }

    public function confirmCurriOrder(Staff $staff, Order $order): bool
    {
        $orderDelivery = $order->orderDelivery;
        /** @var CurriDelivery $curriDelivery */
        $curriDelivery = $orderDelivery->deliverable;

        return $order->isProcessor($staff) && $order->isApproved() && $orderDelivery->isCurriDelivery() && !$curriDelivery->isConfirmedBySupplier() && $orderDelivery->isAValidDateTimeForSupplier();
    }

    public function confirmNoticeEnRouteCurriDelivery(Staff $staff, Order $order): bool
    {
        $orderDelivery = $order->orderDelivery;
        /** @var CurriDelivery $curriDelivery */
        $curriDelivery = $orderDelivery->deliverable;

        return $order->isProcessor($staff) && $order->isCompleted() && $orderDelivery->isCurriDelivery() && $curriDelivery->isBooked();
    }

    public function getCurriDeliveryPrice(Staff $staff, Order $order): bool
    {
        $orderDelivery = $order->orderDelivery;
        /** @var CurriDelivery $curriDelivery */
        $curriDelivery = $orderDelivery->deliverable;

        return $order->isProcessor($staff) && $order->isApproved() && $orderDelivery->isCurriDelivery() && !$curriDelivery->isConfirmedBySupplier();
    }

    public function createCustomItem(Staff $staff, Order $order): bool
    {
        $items = $order->items->filter->isCustomItem()->values();

        return $items->count() < 10;
    }

    public function preApprove(Staff $staff, Order $order): bool
    {
        $orderDelivery   = $order->orderDelivery;
        $isCurriDelivery = $orderDelivery->isCurriDelivery();
        $deliveryFee     = $orderDelivery->fee ?? 0;

        return $order->isProcessor($staff) && $order->isPending() && $order->isAssigned() && $order->doesntHavePendingItems() && (!$isCurriDelivery || $deliveryFee > 0);
    }

    public function read(Staff $staff, Order $order): bool
    {
        return $order->isProcessor($staff);
    }

    public function reopen(Staff $staff, Order $order): bool
    {
        return $order->isProcessor($staff) && $order->isPendingApproval() && $order->isAssigned() && $order->hasAvailability() && $order->doesntHavePendingItems();
    }

    public function sendForApproval(Staff $staff, Order $order): bool
    {
        return $this->preApprove($staff, $order) && $order->hasAvailability();
    }

    public function update(Staff $staff, Order $order): bool
    {
        return $order->isProcessor($staff) && $order->isPending() && $order->isAssigned();
    }
}

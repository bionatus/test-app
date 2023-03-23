<?php

namespace App\Policies\LiveApi\V1;

use App\Models\ItemOrder;
use App\Models\Staff;
use App\Models\Supplier;
use Illuminate\Auth\Access\HandlesAuthorization;

class ItemOrderPolicy
{
    use HandlesAuthorization;

    public function delete(Staff $staff, ItemOrder $itemOrder): bool
    {
        $item = $itemOrder->item;
        if (!$item->isCustomItem()) {
            return false;
        }

        /** @var \App\Models\CustomItem $customItem */
        $customItem = $item->orderable;
        $supplier   = $itemOrder->order->supplier;

        return $customItem->creator_type === Supplier::MORPH_ALIAS && $supplier->getKey() === $customItem->creator_id;
    }

    public function removeItemOrderInProgress(Staff $staff, ItemOrder $itemOrder): bool
    {
        return $itemOrder->order->isProcessor($staff) && ($itemOrder->order->isApproved() || $itemOrder->order->isCompleted()) && !$itemOrder->isRemoved();
    }
}

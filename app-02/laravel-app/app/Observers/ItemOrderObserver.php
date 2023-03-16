<?php

namespace App\Observers;

use App\Models\ItemOrder;
use Illuminate\Support\Str;

class ItemOrderObserver
{
    public function creating(ItemOrder $itemOrder): void
    {
        $itemOrder->uuid ??= Str::uuid();
    }

    public function saved(ItemOrder $itemOrder): void
    {
        $itemOrder->order()->touch();
    }
}

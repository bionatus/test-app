<?php

namespace App\Nova\Observers;

use App\Models\Item;
use App\Models\Supply;

class SupplyObserver
{
    public function creating(Supply $supply)
    {
        $item = Item::create([
            'type' => Item::TYPE_SUPPLY,
        ]);

        $supply->id = $item->getKey();
    }

    public function deleted(Supply $supply)
    {
        $supply->item->delete();
    }
}

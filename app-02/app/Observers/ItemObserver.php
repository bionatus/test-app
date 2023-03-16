<?php

namespace App\Observers;

use App\Models\Item;
use Illuminate\Support\Str;

class ItemObserver
{
    public function creating(Item $item): void
    {
        $item->uuid ??= Str::uuid();
    }
}

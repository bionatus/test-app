<?php

namespace App\Rules\ItemOrder;

use App\Models\ItemOrder;
use App\Models\Scopes\ByRouteKey;
use Illuminate\Contracts\Validation\Rule;

class ValidQuantity implements Rule
{
    private $items;

    public function __construct($items)
    {
        if (!is_array($items)) {
            return false;
        }

        $this->items = $items;
    }

    public function passes($attribute, $value): bool
    {
        $items = $this->items;

        $key = explode('.', $attribute)[1];

        $itemOrderRouteKey = $items[$key]['uuid'];

        /** @var ItemOrder $itemOrder */
        $itemOrder = ItemOrder::scoped(new ByRouteKey($itemOrderRouteKey))->first();

        return $value <= $itemOrder->quantity_requested;
    }

    public function message(): string
    {
        return 'Invalid quantity.';
    }
}

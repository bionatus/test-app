<?php

namespace App\Rules\ItemOrder;

use App\Models\Item;
use App\Models\Replacement;
use App\Models\Scopes\ByRouteKey;
use Illuminate\Contracts\Validation\Rule;

class ValidReplacement implements Rule
{
    private Item $item;

    public function __construct(Item $item)
    {
        $this->item = $item;
    }

    public function passes($attribute, $value): bool
    {
        /** @var Replacement $replacement */
        $replacement = Replacement::scoped(new ByRouteKey($value))->first();

        if ($replacement->original_part_id === $this->item->getKey()) {
            return true;
        }

        return false;
    }

    public function message(): string
    {
        return 'This :attribute is not valid to replace the item';
    }
}

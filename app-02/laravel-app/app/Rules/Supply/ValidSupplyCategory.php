<?php

namespace App\Rules\Supply;

use App\Models\Scopes\ByRouteKey;
use App\Models\SupplyCategory;
use Illuminate\Contracts\Validation\Rule;

class ValidSupplyCategory implements Rule
{
    public function passes($attribute, $value)
    {
        $supplyCategory = SupplyCategory::scoped(new ByRouteKey($value))->first();

        return $supplyCategory->children()->doesntExist();
    }

    public function message()
    {
        return 'Invalid supply category.';
    }
}

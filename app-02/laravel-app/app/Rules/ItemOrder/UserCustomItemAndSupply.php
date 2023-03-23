<?php

namespace App\Rules\ItemOrder;

use App\Models\ItemOrder;
use App\Models\ItemOrder\Scopes\IsUserCustomItemOrSupply;
use App\Models\Scopes\ByUuid;
use Illuminate\Contracts\Validation\Rule;

class UserCustomItemAndSupply implements Rule
{
    public function passes($attribute, $value)
    {
        return ItemOrder::scoped(new ByUuid($value))->scoped(new IsUserCustomItemOrSupply())->exists();
    }

    public function message()
    {
        return 'The item should be type supply or custom item added by the technician.';
    }
}

<?php

namespace App\Rules\Item;

use App\Models\Item;
use App\Models\Scopes\ByCreatorType;
use App\Models\Scopes\ByRouteKey;
use App\Models\Scopes\ByType;
use App\Models\User;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

class UserCustomItemAndSupply implements Rule
{
    public function passes($attribute, $value)
    {
        return Item::scoped(new ByRouteKey($value))->where(function(Builder $builder) {
            $builder->scoped(new ByType(Item::TYPE_CUSTOM_ITEM))->whereHas('customItem', function(Builder $builder) {
                $builder->scoped(new ByCreatorType(User::MORPH_ALIAS));
            })->orWhere(function(Builder $builder) {
                $builder->scoped(new ByType(Item::TYPE_SUPPLY));
            });
        })->exists();
    }

    public function message()
    {
        return 'The item should exist and be type supply or custom item added by the technician.';
    }
}

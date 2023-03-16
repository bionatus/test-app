<?php

namespace App\Rules\SupportCallCategory;

use App\Models\Scopes\ByRouteKey;
use App\Models\SupportCall;
use App\Models\SupportCallCategory;
use Illuminate\Contracts\Validation\Rule;

class ValidCategory implements Rule
{
    public function passes($attribute, $value): bool
    {
        if (SupportCall::CATEGORY_OEM === $value || SupportCall::CATEGORY_MISSING_OEM === $value) {
            return true;
        }

        return SupportCallCategory::whereDoesntHave('children')->scoped(new ByRouteKey($value))->exists();
    }

    public function message(): string
    {
        return 'Invalid support call category.';
    }
}

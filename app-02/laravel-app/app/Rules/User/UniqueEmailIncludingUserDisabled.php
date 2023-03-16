<?php

namespace App\Rules\User;

use App\Models\User;
use Illuminate\Contracts\Validation\Rule;
use Lang;

class UniqueEmailIncludingUserDisabled implements Rule
{
    public function passes($attribute, $value): bool
    {
        return !User::where('email', $value)->whereNotNull('disabled_at')->first();
    }

    public function message(): string
    {
        return Lang::get('auth.account_disabled');
    }
}

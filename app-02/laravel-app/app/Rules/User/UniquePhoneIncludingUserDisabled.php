<?php

namespace App\Rules\User;

use App\Models\Phone;
use Illuminate\Contracts\Validation\Rule;
use Lang;

class UniquePhoneIncludingUserDisabled implements Rule
{
    public function passes($attribute, $value): bool
    {
        $phone = Phone::where('number', $value)->with('user')->first();

        return empty($phone) || !$phone->user()->whereNotNull('disabled_at')->first();
    }

    public function message(): string
    {
        return Lang::get('auth.account_disabled');
    }
}

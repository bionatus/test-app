<?php

namespace App\Rules\XoxoRedemption;

use Auth;
use Illuminate\Contracts\Validation\Rule;

class AvailablePoints implements Rule
{
    public function passes($attribute, $value)
    {
        $user = Auth::user();

        return $user->availablePointsToCash() >= $value;
    }

    public function message()
    {
        return 'Not enough funds.';
    }
}

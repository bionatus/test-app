<?php

namespace App\Rules\Phone;

use App\Models\AuthenticationCode;
use App\Models\Phone;
use App\Models\Phone\Scopes\ByFullNumber;
use Illuminate\Contracts\Validation\Rule;
use Str;

class FullNumberExist implements Rule
{
    private string             $message;
    private AuthenticationCode $authenticationCode;

    public function passes($attribute, $value)
    {
        if (!Str::startsWith($value, '+')) {
            $this->message = 'The :attribute is not properly formatted.';

            return false;
        }

        $fullNumber = Str::substr($value, 1);
        if (!(is_numeric($fullNumber))) {
            $this->message = 'The :attribute is invalid.';
            return false;
        }

        if (!($phone = Phone::query()->scoped(new ByFullNumber($fullNumber))->first())) {
            $this->message = 'The :attribute does not exist in our records.';

            return false;
        }

        if (!($authenticationCode = $phone->authenticationCodes->last())) {
            $this->message = 'The :attribute does not have an authentication code.';

            return false;
        }

        $this->authenticationCode = $authenticationCode;

        return true;
    }

    public function authenticationCode(): AuthenticationCode
    {
        return $this->authenticationCode;
    }

    public function message()
    {
        return $this->message;
    }
}

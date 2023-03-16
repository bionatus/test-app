<?php

namespace App\Http\Requests\Api\V3\Auth\Phone\Register\Assign;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Rules\User\UniqueEmailIncludingUserDisabled;

class InvokeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::EMAIL        => [
                'required',
                'string',
                'bail',
                new UniqueEmailIncludingUserDisabled(),
                'email:strict',
                'ends_with_tld',
                'unique:users',
            ],
            RequestKeys::TOS_ACCEPTED => ['required'],
        ];
    }
}

<?php

namespace App\Http\Requests\LiveApi\V1\ConfirmedUser;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::CUSTOMER_TIER => ['string', 'nullable'],
            RequestKeys::CASH_BUYER    => ['required', 'boolean'],
        ];
    }
}

<?php

namespace App\Http\Requests\LiveApi\V1\User\SupplierUser;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::CASH_BUYER    => ['required', 'boolean'],
            RequestKeys::CUSTOMER_TIER => ['nullable', 'string', 'max:255'],
        ];
    }
}

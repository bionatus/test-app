<?php

namespace App\Http\Requests\LiveApi\V1\Order\Fee;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Rules\MoneyFormat;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        $validations = ['nullable', 'bail', 'numeric', 'min:0', new MoneyFormat()];

        return [
            RequestKeys::DISCOUNT => $validations,
            RequestKeys::TAX      => $validations,
        ];
    }
}

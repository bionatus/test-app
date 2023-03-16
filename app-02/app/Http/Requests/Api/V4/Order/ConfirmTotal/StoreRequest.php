<?php

namespace App\Http\Requests\Api\V4\Order\ConfirmTotal;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [RequestKeys::PAID_TOTAL => ['required', 'numeric', 'min:0', 'max:999999']];
    }
}

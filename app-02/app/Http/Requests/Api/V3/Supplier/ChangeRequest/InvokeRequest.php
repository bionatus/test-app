<?php

namespace App\Http\Requests\Api\V3\Supplier\ChangeRequest;

use App\Constants\RequestKeys;
use App\Constants\SupplierChangeRequestReasons;
use App\Http\Requests\FormRequest;
use Illuminate\Validation\Rule;

class InvokeRequest extends FormRequest
{
    public function rules(): array
    {
        $reason = $this->request->get(RequestKeys::REASON);

        return [
            RequestKeys::REASON => ['required', 'string', rule::in(SupplierChangeRequestReasons::ALL)],
            RequestKeys::DETAIL => [
                Rule::requiredIf(fn() => SupplierChangeRequestReasons::REASON_OTHER == $reason),
                'string',
                'max:1000',
            ],
        ];
    }
}

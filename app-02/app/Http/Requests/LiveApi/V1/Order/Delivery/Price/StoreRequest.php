<?php

namespace App\Http\Requests\LiveApi\V1\Order\Delivery\Price;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\CurriDelivery;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function rules()
    {
        return [
            RequestKeys::VEHICLE_TYPE      => ['required', 'string', Rule::in(CurriDelivery::VEHICLE_TYPES_ALL)],
            RequestKeys::USE_STORE_ADDRESS => ['boolean'],
            RequestKeys::ADDRESS           => [
                'required_unless:' . RequestKeys::USE_STORE_ADDRESS . ',' . true,
                'string',
                'max:255',
            ],
            RequestKeys::ADDRESS_2         => [
                'string',
                'max:255',
            ],
            RequestKeys::CITY              => [
                'required_unless:' . RequestKeys::USE_STORE_ADDRESS . ',' . true,
                'string',
                'max:255',
            ],
            RequestKeys::STATE             => [
                'required_unless:' . RequestKeys::USE_STORE_ADDRESS . ',' . true,
                'string',
                'max:255',
            ],
            RequestKeys::ZIP_CODE          => [
                'required_unless:' . RequestKeys::USE_STORE_ADDRESS . ',' . true,
                'string',
                'digits:5',
            ],
            RequestKeys::COUNTRY           => [
                'required_unless:' . RequestKeys::USE_STORE_ADDRESS . ',' . true,
                'string',
                'max:255',
            ],
        ];
    }
}

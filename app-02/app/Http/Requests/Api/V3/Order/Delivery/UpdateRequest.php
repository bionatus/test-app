<?php

namespace App\Http\Requests\Api\V3\Order\Delivery;

use App\Constants\DeliveryTimeRanges;
use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\OrderDelivery;
use App\Rules\OrderDelivery\ValidEndTime;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        $rules[RequestKeys::TYPE] = ['required', 'string', Rule::in(OrderDelivery::TYPE_LEGACY_ALL)];

        $rules[RequestKeys::REQUESTED_DATE]        = ['required', 'date_format:Y-m-d'];
        $rules[RequestKeys::REQUESTED_START_TIME]  = [
            'required',
            'date_format:H:i',
            Rule::in(DeliveryTimeRanges::ALL_START_TIME),
        ];
        $rules[RequestKeys::REQUESTED_END_TIME]    = [
            'required',
            'date_format:H:i',
            Rule::in(DeliveryTimeRanges::ALL_END_TIME),
            new ValidEndTime($this->get(RequestKeys::REQUESTED_START_TIME)),
        ];
        $rules[RequestKeys::DESTINATION_ADDRESS_1] = [
            'required_unless:' . RequestKeys::TYPE . ',' . OrderDelivery::TYPE_PICKUP,
            'string',
            'max:255',
        ];
        $rules[RequestKeys::DESTINATION_ADDRESS_2] = ['sometimes', 'nullable', 'string', 'max:255'];
        $rules[RequestKeys::DESTINATION_COUNTRY]   = [
            'required_unless:' . RequestKeys::TYPE . ',' . OrderDelivery::TYPE_PICKUP,
            'string',
            'max:255',
        ];
        $rules[RequestKeys::DESTINATION_STATE]     = [
            'required_unless:' . RequestKeys::TYPE . ',' . OrderDelivery::TYPE_PICKUP,
            'string',
            'max:255',
        ];
        $rules[RequestKeys::DESTINATION_CITY]      = [
            'required_unless:' . RequestKeys::TYPE . ',' . OrderDelivery::TYPE_PICKUP,
            'string',
            'max:255',
        ];
        $rules[RequestKeys::DESTINATION_ZIP_CODE]  = [
            'required_unless:' . RequestKeys::TYPE . ',' . OrderDelivery::TYPE_PICKUP,
            'string',
            'max:255',
        ];
        $rules[RequestKeys::NOTE]                  = ['nullable', 'string', 'max:255'];

        return $rules;
    }
}

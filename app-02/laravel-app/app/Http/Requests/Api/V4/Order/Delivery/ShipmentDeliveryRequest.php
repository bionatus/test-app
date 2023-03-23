<?php

namespace App\Http\Requests\Api\V4\Order\Delivery;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\ShipmentDeliveryPreference;
use App\Rules\ProhibitedAttribute;
use Illuminate\Validation\Rule;

class ShipmentDeliveryRequest extends FormRequest
{
    public function rules()
    {
        return [
            RequestKeys::SHIPMENT_PREFERENCE  => [
                'required',
                'string',
                Rule::in(ShipmentDeliveryPreference::PREFERENCES_SLUG),
            ],
            RequestKeys::REQUESTED_DATE       => [new ProhibitedAttribute()],
            RequestKeys::REQUESTED_START_TIME => [new ProhibitedAttribute()],
            RequestKeys::REQUESTED_END_TIME   => [new ProhibitedAttribute()],
        ];
    }
}

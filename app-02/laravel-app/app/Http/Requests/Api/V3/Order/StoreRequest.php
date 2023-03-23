<?php

namespace App\Http\Requests\Api\V3\Order;

use App\Constants\DeliveryTimeRanges;
use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\Item;
use App\Models\Oem;
use App\Models\OrderDelivery;
use App\Models\Supplier;
use App\Rules\OrderDelivery\ValidEndTime;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        $rules[RequestKeys::OEM]                   = [
            'nullable',
            Rule::exists(Oem::tableName(), Oem::routeKeyName()),
        ];
        $rules[RequestKeys::SUPPLIER]              = [
            'required',
            'string',
            Rule::exists(Supplier::tableName(), Supplier::routeKeyName()),
        ];
        $rules[RequestKeys::ITEMS]                 = ['required', 'array'];
        $rules[RequestKeys::ITEMS . '.*']          = ['required'];
        $rules[RequestKeys::ITEMS . '.*.uuid']     = [
            'required',
            'string',
            Rule::exists(Item::tableName(), Item::routeKeyName()),
        ];
        $rules[RequestKeys::ITEMS . '.*.quantity'] = ['required', 'integer', 'min:1'];

        $rules[RequestKeys::TYPE]                  = [
            'required',
            'string',
            Rule::in(OrderDelivery::TYPE_LEGACY_ALL),
        ];
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

    public function messages(): array
    {
        return [
            RequestKeys::ITEMS . '.*.uuid.exists' => 'Each item in items must exist.',
        ];
    }

    public function supplier(): ?Supplier
    {
        return Supplier::where(Supplier::routeKeyName(), $this->get(RequestKeys::SUPPLIER))->first();
    }

    private function zipCode(): ?string
    {
        return $this->supplier() ? $this->supplier()->zip_code : null;
    }

    public function oem(): ?Oem
    {
        $oem = $this->get(RequestKeys::OEM);
        if (empty($oem)) {
            return null;
        }

        return Oem::where(Oem::routeKeyName(), $oem)->first();
    }
}

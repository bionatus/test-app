<?php

namespace App\Http\Requests\Api\V4\Order\Delivery;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\OrderDelivery;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        $curri    = CurriDeliveryRequest::createFrom($this);
        $pickup   = PickupRequest::createFrom($this);
        $shipment = ShipmentDeliveryRequest::createFrom($this);

        $rules = [
            RequestKeys::TYPE          => ['required', 'string', Rule::in(OrderDelivery::TYPE_ALL)],
            RequestKeys::IS_NEEDED_NOW => ['required', 'boolean'],
        ];

        return array_merge($rules,
            $this->get(RequestKeys::TYPE) === OrderDelivery::TYPE_CURRI_DELIVERY ? $curri->rules() : [],
            $this->get(RequestKeys::TYPE) === OrderDelivery::TYPE_PICKUP ? $pickup->rules() : [],
            $this->get(RequestKeys::TYPE) === OrderDelivery::TYPE_SHIPMENT_DELIVERY ? $shipment->rules() : []);
    }
}

<?php

namespace App\Http\Requests\LiveApi\V2\Order\ItemOrder\ExtraItem;

use App\Constants\RequestKeys;
use App\Constants\RouteParameters;
use App\Http\Requests\FormRequest;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Rules\ItemOrder\UserCustomItemAndSupply;
use App\Rules\ItemOrder\ValidQuantity;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        /** @var Order $order */
        $order = $this->route(RouteParameters::ORDER);

        /** @var array $items */
        $items = $this->request->get(RequestKeys::ITEMS);

        $rules[RequestKeys::ITEMS]                 = ['required', 'array'];
        $rules[RequestKeys::ITEMS . '.*']          = ['required'];
        $rules[RequestKeys::ITEMS . '.*.uuid']     = [
            'required',
            'string',
            Rule::exists(ItemOrder::tableName(), ItemOrder::routeKeyName())
                ->where('order_id', $order->getKey())
                ->where('initial_request', true),
            new UserCustomItemAndSupply(),
        ];
        $rules[RequestKeys::ITEMS . '.*.quantity'] = [
            'required',
            'integer',
            'min:0',
            'bail',
            new ValidQuantity($items),
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            RequestKeys::ITEMS . '.*.uuid.exists' => 'Each item in item orders must exist. They must belong to an order and must have been added on the initial request.',
        ];
    }
}

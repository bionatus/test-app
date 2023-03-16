<?php

namespace App\Http\Requests\LiveApi\V1\Order\ItemOrder;

use App\Constants\RequestKeys;
use App\Constants\RouteParameters;
use App\Http\Requests\FormRequest;
use App\Models\Item;
use App\Models\ItemOrder;
use App\Models\Replacement;
use App\Rules\ItemOrder\ValidReplacement;
use App\Rules\ProhibitedAttribute;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {

        $status    = $this->request->get(RequestKeys::STATUS);
        $itemOrder = $this->route(RouteParameters::ITEM_ORDER);

        if ((ItemOrder::STATUS_NOT_AVAILABLE === $status) || (ItemOrder::STATUS_PENDING === $status && $itemOrder->status !== ItemOrder::STATUS_PENDING)) {
            return [
                RequestKeys::QUANTITY => ['integer', 'min:1'],
                RequestKeys::PRICE    => ['numeric', 'min:0'],
                RequestKeys::STATUS   => ['required', 'string', Rule::in(ItemOrder::VALID_STATUSES)],
            ];
        }

        $item = $itemOrder->item;

        $rules = [
            RequestKeys::QUANTITY => ['required', 'integer', 'min:1'],
            RequestKeys::PRICE    => ['required', 'numeric', 'min:0'],
            RequestKeys::STATUS   => ['required', 'string', Rule::in(ItemOrder::VALID_STATUSES)],
        ];

        if ($item->type == Item::TYPE_SUPPLY) {
            $rules[RequestKeys::SUPPLY_DETAIL] = ['nullable', 'string'];
            $rules[RequestKeys::CUSTOM_DETAIL] = [new ProhibitedAttribute()];
            $rules[RequestKeys::REPLACEMENT]   = [new ProhibitedAttribute()];

            return $rules;
        }

        if ($item->type == Item::TYPE_CUSTOM_ITEM) {
            $rules[RequestKeys::CUSTOM_DETAIL] = ['nullable', 'string'];
            $rules[RequestKeys::SUPPLY_DETAIL] = [new ProhibitedAttribute()];
            $rules[RequestKeys::REPLACEMENT]   = [new ProhibitedAttribute()];

            return $rules;
        }

        $rules[RequestKeys::REPLACEMENT]           = ['present', 'nullable', 'array'];
        $rules[RequestKeys::REPLACEMENT . '.type'] = [
            'required_unless:' . RequestKeys::REPLACEMENT . ',null',
            Rule::in(['generic', 'replacement']),
        ];

        $rules[RequestKeys::REPLACEMENT . '.description'] = [
            'required_if:replacement.type,generic',
        ];
        $rules[RequestKeys::REPLACEMENT . '.id']          = [
            'bail',
            'required_if:replacement.type,replacement',
            Rule::exists(Replacement::tableName(), Replacement::routeKeyName()),
            new ValidReplacement($item),
        ];
        $rules[RequestKeys::SUPPLY_DETAIL]                = [new ProhibitedAttribute()];
        $rules[RequestKeys::CUSTOM_DETAIL]                = [new ProhibitedAttribute()];

        return $rules;
    }
}

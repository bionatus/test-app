<?php

namespace App\Http\Requests\LiveApi\V2\Order\ItemOrder\Part;

use App\Constants\RequestKeys;
use App\Constants\RouteParameters;
use App\Http\Requests\FormRequest;
use App\Models\ItemOrder;
use App\Models\Replacement;
use App\Rules\ItemOrder\ValidReplacement;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        $itemOrder             = $this->route(RouteParameters::PART_ITEM_ORDER);
        $validItemOrderStatus  = [ItemOrder::STATUS_AVAILABLE, ItemOrder::STATUS_NOT_AVAILABLE];
        $validReplacementTypes = [ItemOrder::REPLACEMENT_TYPE_GENERIC, ItemOrder::REPLACEMENT_TYPE_REPLACEMENT];

        return [
            RequestKeys::STATUS                       => ['required', 'string', Rule::in($validItemOrderStatus)],
            RequestKeys::REPLACEMENT                  => ['present', 'nullable', 'array'],
            RequestKeys::REPLACEMENT . '.type'        => [
                'required_unless:' . RequestKeys::REPLACEMENT . ',null',
                Rule::in($validReplacementTypes),
            ],
            RequestKeys::REPLACEMENT . '.description' => ['required_if:replacement.type,generic'],
            RequestKeys::REPLACEMENT . '.id'          => [
                'bail',
                'required_if:replacement.type,replacement',
                Rule::exists(Replacement::tableName(), Replacement::routeKeyName()),
                new ValidReplacement($itemOrder->item),
            ],
        ];
    }
}

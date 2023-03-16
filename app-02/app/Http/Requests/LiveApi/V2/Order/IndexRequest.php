<?php

namespace App\Http\Requests\LiveApi\V2\Order;

use App;
use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\Order;
use Illuminate\Validation\Rule;

class IndexRequest extends FormRequest
{
    public function rules()
    {
        return [
            RequestKeys::TYPE => [
                'nullable',
                'string',
                Rule::in([Order::TYPE_ORDER_LIST_AVAILABILITY, Order::TYPE_ORDER_LIST_APPROVED]),
            ],
        ];
    }
}

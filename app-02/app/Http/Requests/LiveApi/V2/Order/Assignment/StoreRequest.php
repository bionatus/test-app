<?php

namespace App\Http\Requests\LiveApi\V2\Order\Assignment;

use App\Constants\RequestKeys;
use App\Constants\RouteParameters;
use App\Http\Requests\FormRequest;
use App\Models\Staff;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        $order    = $this->route(RouteParameters::ORDER);
        $supplier = $order->supplier;

        return [
            RequestKeys::STAFF => [
                'required',
                Rule::exists(Staff::tableName(), Staff::routeKeyName())
                    ->where('type', Staff::TYPE_COUNTER)
                    ->where('supplier_id', $supplier->getKey()),
            ],
        ];
    }
}

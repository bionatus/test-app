<?php

namespace App\Http\Requests\Api\V3\Account\Supplier;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\Supplier;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::SUPPLIER => [
                'required',
                'string',
                Rule::exists(Supplier::tableName(), Supplier::routeKeyName()),
            ],
        ];
    }
}

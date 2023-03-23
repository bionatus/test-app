<?php

namespace App\Http\Requests\Api\V4\Account\Cart;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\Supplier;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
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

<?php

namespace App\Http\Requests\Api\V3\Account\Oem;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\Oem;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::OEM => ['required', Rule::exists(Oem::tableName(), Oem::routeKeyName())],
        ];
    }
}

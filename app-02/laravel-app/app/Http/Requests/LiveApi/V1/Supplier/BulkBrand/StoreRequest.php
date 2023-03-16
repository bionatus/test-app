<?php

namespace App\Http\Requests\LiveApi\V1\Supplier\BulkBrand;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\Brand;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::BRANDS        => ['required', 'array'],
            RequestKeys::BRANDS . '.*' => ['string', Rule::exists(Brand::tableName(), Brand::routeKeyName())],
        ];
    }
}

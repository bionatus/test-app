<?php

namespace App\Http\Requests\Api\V3\Supply;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Rules\Supply\ValidSupplyCategory;

class IndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::SUPPLY_CATEGORY => [
                'bail',
                'required',
                'string',
                'max:255',
                'exists:supply_categories,slug',
                new ValidSupplyCategory,
            ],
            RequestKeys::SEARCH_STRING   => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }
}

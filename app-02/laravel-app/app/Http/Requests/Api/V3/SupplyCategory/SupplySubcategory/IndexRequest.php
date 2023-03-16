<?php

namespace App\Http\Requests\Api\V3\SupplyCategory\SupplySubcategory;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class IndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::SEARCH_STRING => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }
}

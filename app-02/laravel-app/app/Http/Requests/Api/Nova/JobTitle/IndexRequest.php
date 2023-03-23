<?php

namespace App\Http\Requests\Api\Nova\JobTitle;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Types\CompanyDataType;
use Illuminate\Validation\Rule;

class IndexRequest extends FormRequest
{
    public function rules()
    {
        return [
            RequestKeys::COMPANY_TYPE => [
                'required',
                'string',
                Rule::in(CompanyDataType::ALL_COMPANY_TYPES),
            ],
        ];
    }
}

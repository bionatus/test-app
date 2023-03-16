<?php

namespace App\Http\Requests\LiveApi\V2\Supplier\Staff;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\Staff;
use Illuminate\Validation\Rule;

class IndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::TYPE => [
                'nullable',
                'string',
                Rule::in(Staff::STAFF_TYPES),
            ],
        ];
    }
}

<?php

namespace App\Http\Requests\Api\V3\SupportCall;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\Oem;
use App\Models\SupportCall;
use App\Rules\SupportCallCategory\ValidCategory;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        $category = $this->request->get(RequestKeys::CATEGORY);

        return [
            RequestKeys::CATEGORY => [
                'required',
                new ValidCategory(),
            ],
            RequestKeys::OEM      => [
                Rule::requiredIf(fn() => $category === SupportCall::CATEGORY_OEM),
                'prohibited_unless:' . RequestKeys::CATEGORY . ',' . SupportCall::CATEGORY_OEM,
                Rule::exists(Oem::tableName(), Oem::routeKeyName()),
            ],
        ];
    }
}

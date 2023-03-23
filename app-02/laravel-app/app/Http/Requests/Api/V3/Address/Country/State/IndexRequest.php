<?php

namespace App\Http\Requests\Api\V3\Address\Country\State;

use App\Constants\Locales;
use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use Illuminate\Validation\Rule;

class IndexRequest extends FormRequest
{
    public function rules()
    {
        return [
            RequestKeys::LOCALE => [
                Rule::in([Locales::EN, Locales::ES]),
            ],
        ];
    }
}

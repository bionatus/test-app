<?php

namespace App\Http\Requests\Api\V3\OrderSupplier;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Rules\Location\Format;
use App\Rules\Location\Latitude;
use App\Rules\Location\Longitude;

class IndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::SEARCH_STRING => ['nullable', 'string', 'min:2', 'max:30'],
            RequestKeys::LOCATION      => ['nullable', 'bail', new Format(), new Latitude(), new Longitude()],
        ];
    }
}

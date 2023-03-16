<?php

namespace App\Http\Requests\LiveApi\V1\Part\RecommendedReplacement;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::BRAND       => ['required', 'string', 'min:2', 'max:255'],
            RequestKeys::PART_NUMBER => ['required', 'string', 'min:2', 'max:255'],
            RequestKeys::NOTE        => ['nullable', 'string', 'max:255'],
        ];
    }
}

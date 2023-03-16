<?php

namespace App\Http\Requests\Api\V2\Tag;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\Brand;
use App\Models\Series;
use App\Models\Tag;
use App\Rules\ProhibitedAttribute;
use Illuminate\Validation\Rule;

class IndexRequest extends FormRequest
{
    public function rules()
    {
        $rules = [
            RequestKeys::PER_PAGE => ['nullable'],
            RequestKeys::TYPE     => ['nullable', 'string', Rule::in(array_keys(Tag::MORPH_MODEL_MAPS))],
            RequestKeys::BRAND    => ['nullable', Rule::exists(Brand::tableName(), Brand::routeKeyName())],
            RequestKeys::SERIES   => ['nullable', Rule::exists(Series::tableName(), Series::routeKeyName())],
        ];

        if ($this->has(RequestKeys::BRAND)) {
            $rules[RequestKeys::TYPE]   = ['required', 'string', Rule::in([Tag::TYPE_SERIES])];
            $rules[RequestKeys::SERIES] = [new ProhibitedAttribute()];
        }

        if ($this->has(RequestKeys::SERIES)) {
            $rules[RequestKeys::TYPE]  = ['required', 'string', Rule::in([Tag::TYPE_MODEL_TYPE])];
            $rules[RequestKeys::BRAND] = [new ProhibitedAttribute()];
        }

        return $rules;
    }
}

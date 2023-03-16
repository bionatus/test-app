<?php

namespace App\Http\Requests\Api\V3\Activity;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\Activity;
use Illuminate\Validation\Rule;

class IndexRequest extends FormRequest
{
    public function rules()
    {
        return [RequestKeys::LOG_NAME => ['nullable', 'string', Rule::in(Activity::TYPE_ALL)]];
    }
}

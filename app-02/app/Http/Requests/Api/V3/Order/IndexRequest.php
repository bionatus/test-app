<?php

namespace App\Http\Requests\Api\V3\Order;

use App;
use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\Status;
use Illuminate\Validation\Rule;

class IndexRequest extends FormRequest
{
    public function rules()
    {
        return [
            RequestKeys::STATUS => ['nullable', 'bail', 'string', Rule::in(Status::STATUSES_NAME)],
        ];
    }
}

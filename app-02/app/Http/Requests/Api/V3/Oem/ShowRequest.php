<?php

namespace App\Http\Requests\Api\V3\Oem;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class ShowRequest extends FormRequest
{
    public function rules()
    {
        return [
            RequestKeys::VERSION => ['required', 'string', 'regex:/^(?:(\d+)\.)(?:(\d+)\.)(\*|\d+)$/'],
        ];
    }
}

<?php

namespace App\Http\Requests\Api\V3\Account\Term\Accept;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;

class InvokeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::TOS_ACCEPTED => ['required', 'accepted'],
        ];
    }
}

<?php

namespace App\Http\Requests\Api\V2\Twilio\Token;

use App\Constants\OperatingSystems;
use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::OS => ['required', 'string', Rule::in([OperatingSystems::ANDROID, OperatingSystems::IOS])],
        ];
    }
}

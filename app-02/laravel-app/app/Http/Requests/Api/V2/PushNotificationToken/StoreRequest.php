<?php

namespace App\Http\Requests\Api\V2\PushNotificationToken;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\PushNotificationToken;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function rules()
    {
        return [
            RequestKeys::OS      => [
                'required',
                'string',
                Rule::in([PushNotificationToken::OS_ANDROID, PushNotificationToken::OS_IOS]),
            ],
            RequestKeys::DEVICE  => ['required', 'string', 'min:10', 'max:255'],
            RequestKeys::VERSION => ['string', 'regex:/^(?:(\d+)\.)(?:(\d+)\.)(\*|\d+)$/'],
            RequestKeys::TOKEN   => ['required', 'string', 'min:10'],
        ];
    }
}

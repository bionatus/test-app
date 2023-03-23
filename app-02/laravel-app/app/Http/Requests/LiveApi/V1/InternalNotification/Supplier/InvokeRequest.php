<?php

namespace App\Http\Requests\LiveApi\V1\InternalNotification\Supplier;

use App\Constants\InternalNotificationsSourceEvents;
use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\User;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\In;

class InvokeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::DATA         => ['nullable', 'array'],
            RequestKeys::MESSAGE      => ['required', 'string', 'max:255'],
            RequestKeys::SOURCE_EVENT => [
                'required',
                'string',
                new In(InternalNotificationsSourceEvents::SUPPLIER_SOURCE_EVENTS),
            ],
            RequestKeys::USER_ID      => ['required', 'integer', new Exists(User::tableName(), User::keyName())],
        ];
    }
}

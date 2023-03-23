<?php

namespace App\Http\Requests\Api\V3\Auth\Phone\Register\Verify;

use App\Constants\RequestKeys;
use App\Constants\RouteParameters;
use App\Http\Requests\FormRequest;
use App\Models\AuthenticationCode;
use App\Models\Phone;
use Illuminate\Validation\Rule;

class InvokeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::CODE => [
                'required',
                'integer',
                'digits:6',
                Rule::exists(AuthenticationCode::tableName(), 'code')
                    ->where('phone_id', $this->phone()->getKey())
                    ->where('type', AuthenticationCode::TYPE_VERIFICATION),
            ],
        ];
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function phone(): Phone
    {
        return $this->route(RouteParameters::UNVERIFIED_PHONE) ?? new Phone();
    }
}

<?php

namespace App\Http\Requests\Api\V3\Account\Point\XoxoVoucher\Redeem;

use App\Constants\RequestKeys;
use App\Constants\RouteParameters;
use App\Http\Requests\FormRequest;
use App\Rules\XoxoRedemption\AvailablePoints;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::DENOMINATION => [
                'required',
                'integer',
                Rule::in($this->route(RouteParameters::VOUCHER)->denominations),
                new AvailablePoints(),
            ],
        ];
    }
}

<?php

namespace App\Http\Requests\Api\V4\Order\Delivery\Address;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Rules\InCountryStates;
use App\Rules\InValidCountries;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            RequestKeys::DESTINATION_ADDRESS_1 => [
                'required',
                'string',
                'max:255',
            ],
            RequestKeys::DESTINATION_ADDRESS_2 => [
                'nullable',
                'string',
                'max:255',
            ],
            RequestKeys::DESTINATION_COUNTRY   => [
                'required',
                'string',
                new InValidCountries(),
            ],
            RequestKeys::DESTINATION_STATE     => [
                'required',
                'string',
                new InCountryStates($this->get(RequestKeys::DESTINATION_COUNTRY)),
            ],
            RequestKeys::DESTINATION_CITY      => [
                'required',
                'string',
                'max:255',
            ],
            RequestKeys::DESTINATION_ZIP_CODE  => [
                'required',
                'string',
                'bail',
                'digits:5',
            ],
            RequestKeys::NOTE                  => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }
}

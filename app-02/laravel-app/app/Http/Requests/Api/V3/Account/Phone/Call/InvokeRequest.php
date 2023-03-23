<?php

namespace App\Http\Requests\Api\V3\Account\Phone\Call;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\Phone;
use App\Models\Phone\Scopes\ByCountryCode;
use App\Models\Phone\Scopes\ByNumber;
use App\Types\CountryDataType;
use Illuminate\Validation\Rule;

class InvokeRequest extends FormRequest
{
    public function rules(): array
    {
        $countryCode = $this->request->get(RequestKeys::COUNTRY_CODE);

        return [
            RequestKeys::COUNTRY_CODE => ['required', Rule::in(CountryDataType::getPhoneCodes())],
            RequestKeys::PHONE        => [
                'required',
                'integer',
                'digits_between:7,15',
                Rule::unique('phones', 'number')->where(fn($query) => $query->where('country_code', $countryCode)
                    ->whereNotNull('verified_at')),
            ],
        ];
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function phone(): ?Phone
    {

        $countryCode = $this->get(RequestKeys::COUNTRY_CODE);
        $number      = $this->get(RequestKeys::PHONE);
        if (!is_numeric($countryCode) || !is_numeric($number)) {
            return null;
        }

        return Phone::scoped(new ByCountryCode($countryCode))->scoped(new ByNumber($number))->first();
    }
}

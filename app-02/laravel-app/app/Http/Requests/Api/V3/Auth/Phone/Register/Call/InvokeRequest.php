<?php

namespace App\Http\Requests\Api\V3\Auth\Phone\Register\Call;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\Phone;
use App\Models\Phone\Scopes\ByCountryCode;
use App\Models\Phone\Scopes\ByNumber;
use App\Rules\User\UniquePhoneIncludingUserDisabled;
use App\Types\CountryDataType;
use Illuminate\Database\Query\Builder;
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
                new UniquePhoneIncludingUserDisabled(),
                Rule::unique(Phone::class, 'number')->where('country_code', $countryCode)->where(function(Builder $query
                ) {
                    $query->whereNotNull('user_id')->orWhereNotNull('verified_at');
                }),
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

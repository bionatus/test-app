<?php

namespace App\Http\Requests\Api\V3\Auth\Phone\Register\Sms;

use App;
use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\Phone;
use App\Rules\Phone\SmsAvailable;
use App\Rules\User\UniquePhoneIncludingUserDisabled;
use App\Types\CountryDataType;
use Illuminate\Validation\Rule;

class InvokeRequest extends FormRequest
{
    private SmsAvailable $smsAvailable;

    public function __construct(
        array $query = [],
        array $request = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        $content = null
    ) {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);

        $this->smsAvailable = App::make(SmsAvailable::class);
    }

    public function rules(): array
    {
        $countryCode = $this->request->get(RequestKeys::COUNTRY_CODE);

        return [
            RequestKeys::COUNTRY_CODE => ['required', Rule::in(CountryDataType::getPhoneCodes())],
            RequestKeys::PHONE        => [
                'required',
                'integer',
                'digits_between:7,15',
                $this->smsAvailable,
                new UniquePhoneIncludingUserDisabled(),
                Rule::unique(Phone::class, 'number')
                    ->where('country_code', $countryCode)
                    ->whereNotNull('user_id')
                    ->whereNotNull('created_at'),
            ],
            RequestKeys::TOS_ACCEPTED => ['required', 'accepted'],
        ];
    }

    public function phone(): ?Phone
    {
        return $this->smsAvailable->phone();
    }
}

<?php

namespace App\Http\Requests\LiveApi\V1\Supplier;

use App\Constants\RequestKeys;
use App\Constants\Timezones;
use App\Http\Requests\FormRequest;
use App\Models\Supplier;
use App\Rules\InCountryStates;
use App\Rules\InValidCountries;
use Auth;
use Config;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        $supplier = Auth::user()->supplier;

        return [
            RequestKeys::NAME                                                          => [
                'required',
                'string',
                'max:255',
            ],
            RequestKeys::EMAIL                                                         => [
                'required',
                'string',
                'max:255',
                'bail',
                'email:strict',
                'ends_with_tld',
                Rule::unique(Supplier::tableName())->ignore($supplier),
            ],
            RequestKeys::BRANCH                                                        => [
                'nullable',
                'bail',
                'integer',
                'min:1',
                'digits_between:1,8',
                Rule::unique(Supplier::tableName(), 'branch')
                    ->where('name', $this->get(RequestKeys::NAME))
                    ->ignore($supplier),
            ],
            RequestKeys::PHONE                                                         => [
                'required',
                'integer',
                'digits_between:7,15',
            ],
            RequestKeys::PROKEEP_PHONE                                                 => [
                'nullable',
                'integer',
                'digits_between:7,15',
            ],
            RequestKeys::ADDRESS                                                       => [
                'required',
                'string',
                'max:255',
            ],
            RequestKeys::ADDRESS_2                                                     => [
                'nullable',
                'string',
                'max:255',
            ],
            RequestKeys::COUNTRY                                                       => [
                'required',
                'string',
                new InValidCountries(),
            ],
            RequestKeys::TIMEZONE                                                      => [
                'required',
                'string',
                Rule::in(Timezones::ALLOWED_TIMEZONES),
            ],
            RequestKeys::STATE                                                         => [
                'required',
                'string',
                new InCountryStates($this->request->get(RequestKeys::COUNTRY)),
            ],
            RequestKeys::CITY                                                          => [
                'required',
                'string',
                'max:255',
            ],
            RequestKeys::ZIP_CODE                                                      => [
                'required',
                'string',
                'bail',
                'digits:5',
            ],
            RequestKeys::CONTACT_PHONE                                                 => [
                'required',
                'integer',
                'digits_between:7,15',
            ],
            RequestKeys::CONTACT_EMAIL                                                 => [
                'required',
                'bail',
                'string',
                'max:255',
                'email:strict',
                'ends_with_tld',
            ],
            RequestKeys::CONTACT_SECONDARY_EMAIL                                       => [
                'nullable',
                'bail',
                'max:255',
                'string',
                'email:strict',
                'ends_with_tld',
            ],
            RequestKeys::MANAGER_NAME                                                  => [
                'required',
                'string',
                'max:255',
            ],
            RequestKeys::MANAGER_EMAIL                                                 => [
                'required',
                'bail',
                'string',
                'max:255',
                'email:strict',
                'ends_with_tld',
            ],
            RequestKeys::MANAGER_PHONE                                                 => [
                'required',
                'integer',
                'digits_between:7,15',
            ],
            RequestKeys::ACCOUNTANT_NAME                                               => [
                'required',
                'string',
                'max:255',
            ],
            RequestKeys::ACCOUNTANT_EMAIL                                              => [
                'required',
                'bail',
                'max:255',
                'string',
                'email:strict',
                'ends_with_tld',
            ],
            RequestKeys::ACCOUNTANT_PHONE                                              => [
                'required',
                'integer',
                'digits_between:7,15',
            ],
            RequestKeys::COUNTER_STAFF                                                 => [
                'required',
                'array',
                'max:10',
            ],
            RequestKeys::COUNTER_STAFF . '.*.' . RequestKeys::NAME                     => [
                'required',
                'string',
                'max:255',
            ],
            RequestKeys::COUNTER_STAFF . '.*.' . RequestKeys::EMAIL                    => [
                'nullable',
                'bail',
                'string',
                'max:255',
                'email:strict',
                'ends_with_tld',
            ],
            RequestKeys::COUNTER_STAFF . '.*.' . RequestKeys::PHONE                    => [
                'nullable',
                'integer',
                'digits_between:7,15',
            ],
            RequestKeys::COUNTER_STAFF . '.*.' . RequestKeys::STAFF_EMAIL_NOTIFICATION => ['nullable', 'boolean'],
            RequestKeys::COUNTER_STAFF . '.*.' . RequestKeys::STAFF_SMS_NOTIFICATION   => ['nullable', 'boolean'],
            RequestKeys::OFFERS_DELIVERY                                               => ['nullable', 'boolean'],
            RequestKeys::IMAGE                                                         => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,gif,heic',
                'max:' . Config::get('media-library.max_stores_file_size') / 1024,
            ],
            RequestKeys::LOGO                                                          => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,gif,heic',
                'max:' . Config::get('media-library.max_stores_file_size') / 1024,
            ],
            RequestKeys::ABOUT                                                         => ['nullable', 'string'],
        ];
    }
}

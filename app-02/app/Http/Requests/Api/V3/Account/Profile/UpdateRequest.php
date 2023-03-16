<?php

namespace App\Http\Requests\Api\V3\Account\Profile;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\User;
use App\Rules\InCountryStates;
use App\Rules\InValidCountries;
use App\Types\CompanyDataType;
use Auth;
use Config;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        $companyType    = $this->request->get(RequestKeys::COMPANY_TYPE);
        $validJobTitles = is_string($companyType) ? CompanyDataType::getJobTitles($companyType) : [];

        return [
            RequestKeys::PHOTO                  => [
                'sometimes',
                'required',
                'file',
                'mimes:jpg,jpeg,png,gif,heic',
                'max:' . Config::get('media-library.max_file_size') / 1024,
            ],
            RequestKeys::FIRST_NAME             => ['sometimes', 'required', 'string'],
            RequestKeys::LAST_NAME              => ['sometimes', 'required', 'string'],
            RequestKeys::EXPERIENCE             => ['sometimes', 'nullable', 'integer'],
            RequestKeys::PUBLIC_NAME            => [
                'sometimes',
                'required',
                'regex:/^[A-Za-z].*$/',
                'alpha_dash',
                Rule::unique(User::tableName())->ignore(Auth::user()),
            ],
            RequestKeys::BIO                    => ['sometimes', 'nullable', 'string'],
            RequestKeys::ADDRESS                => ['sometimes', 'required', 'string'],
            RequestKeys::ADDRESS_2              => ['sometimes', 'nullable', 'string'],
            RequestKeys::COUNTRY                => ['sometimes', 'required', 'string', new InValidCountries()],
            RequestKeys::STATE                  => [
                'sometimes',
                'required',
                'string',
                new InCountryStates($this->request->get(RequestKeys::COUNTRY)),
            ],
            RequestKeys::CITY                   => ['sometimes', 'required', 'string'],
            RequestKeys::ZIP_CODE               => ['sometimes', 'required', 'string', 'bail', 'digits:5'],
            RequestKeys::COMPANY_NAME           => [
                'required_with:' . implode(',', [
                    RequestKeys::COMPANY_TYPE,
                    RequestKeys::COMPANY_CITY,
                    RequestKeys::COMPANY_STATE,
                    RequestKeys::COMPANY_COUNTRY,
                    RequestKeys::COMPANY_ZIP_CODE,
                    RequestKeys::COMPANY_ADDRESS,
                    RequestKeys::JOB_TITLE,
                ]),
                'string',
            ],
            RequestKeys::COMPANY_TYPE           => [
                'required_with:' . implode(',', [
                    RequestKeys::COMPANY_NAME,
                    RequestKeys::COMPANY_CITY,
                    RequestKeys::COMPANY_STATE,
                    RequestKeys::COMPANY_COUNTRY,
                    RequestKeys::COMPANY_ZIP_CODE,
                    RequestKeys::COMPANY_ADDRESS,
                    RequestKeys::JOB_TITLE,
                ]),
                'string',
                Rule::in(CompanyDataType::ALL_COMPANY_TYPES),
            ],
            RequestKeys::COMPANY_COUNTRY        => [
                'required_with:' . implode(',', [
                    RequestKeys::COMPANY_NAME,
                    RequestKeys::COMPANY_TYPE,
                    RequestKeys::COMPANY_CITY,
                    RequestKeys::COMPANY_STATE,
                    RequestKeys::COMPANY_ZIP_CODE,
                    RequestKeys::COMPANY_ADDRESS,
                    RequestKeys::JOB_TITLE,
                ]),
                'string',
                new InValidCountries(),
            ],
            RequestKeys::COMPANY_STATE          => [
                'required_with:' . implode(',', [
                    RequestKeys::COMPANY_NAME,
                    RequestKeys::COMPANY_TYPE,
                    RequestKeys::COMPANY_CITY,
                    RequestKeys::COMPANY_COUNTRY,
                    RequestKeys::COMPANY_ZIP_CODE,
                    RequestKeys::COMPANY_ADDRESS,
                    RequestKeys::JOB_TITLE,
                ]),
                'bail',
                'string',
                new InCountryStates($this->get(RequestKeys::COMPANY_COUNTRY)),
            ],
            RequestKeys::COMPANY_CITY           => [
                'required_with:' . implode(',', [
                    RequestKeys::COMPANY_NAME,
                    RequestKeys::COMPANY_TYPE,
                    RequestKeys::COMPANY_STATE,
                    RequestKeys::COMPANY_COUNTRY,
                    RequestKeys::COMPANY_ZIP_CODE,
                    RequestKeys::COMPANY_ADDRESS,
                    RequestKeys::JOB_TITLE,
                ]),
                'string',
            ],
            RequestKeys::COMPANY_ZIP_CODE       => [
                'required_with:' . implode(',', [
                    RequestKeys::COMPANY_NAME,
                    RequestKeys::COMPANY_TYPE,
                    RequestKeys::COMPANY_CITY,
                    RequestKeys::COMPANY_STATE,
                    RequestKeys::COMPANY_COUNTRY,
                    RequestKeys::COMPANY_ADDRESS,
                    RequestKeys::JOB_TITLE,
                ]),
                'string',
                'bail',
                'digits:5',
            ],
            RequestKeys::COMPANY_ADDRESS           => [
                'required_with:' . implode(',', [
                    RequestKeys::COMPANY_NAME,
                    RequestKeys::COMPANY_TYPE,
                    RequestKeys::COMPANY_CITY,
                    RequestKeys::COMPANY_STATE,
                    RequestKeys::COMPANY_COUNTRY,
                    RequestKeys::COMPANY_ZIP_CODE,
                    RequestKeys::JOB_TITLE,
                ]),
                'string',
            ],
            RequestKeys::JOB_TITLE              => [
                'required_with:' . implode(',', [
                    RequestKeys::COMPANY_NAME,
                    RequestKeys::COMPANY_TYPE,
                    RequestKeys::COMPANY_CITY,
                    RequestKeys::COMPANY_STATE,
                    RequestKeys::COMPANY_COUNTRY,
                    RequestKeys::COMPANY_ZIP_CODE,
                    RequestKeys::COMPANY_ADDRESS,
                ]),
                'string',
                Rule::in($validJobTitles),
            ],
            RequestKeys::PRIMARY_EQUIPMENT_TYPE => [
                'exclude_unless:' . RequestKeys::COMPANY_TYPE . ',' . CompanyDataType::TYPE_CONTRACTOR,
                'string',
                Rule::in(CompanyDataType::ALL_EQUIPMENT_TYPES),
            ],
            RequestKeys::HAT_REQUESTED          => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function messages()
    {
        return [
            RequestKeys::PUBLIC_NAME . '.regex' => 'The :attribute must start with a letter.',
        ];
    }
}

<?php

namespace App\Http\Requests\Api\V4\Account\Profile;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\Company;
use App\Models\Scopes\ByRouteKey;
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
        $rules = [
            RequestKeys::PHOTO       => [
                'sometimes',
                'required',
                'file',
                'mimes:jpg,jpeg,png,gif,heic',
                'max:' . Config::get('media-library.max_file_size') / 1024,
            ],
            RequestKeys::FIRST_NAME  => ['sometimes', 'required', 'string'],
            RequestKeys::LAST_NAME   => ['sometimes', 'required', 'string'],
            RequestKeys::EXPERIENCE  => ['sometimes', 'nullable', 'integer'],
            RequestKeys::PUBLIC_NAME => [
                'sometimes',
                'required',
                'regex:/^[A-Za-z].*$/',
                'alpha_dash',
                Rule::unique(User::tableName())->ignore(Auth::user()),
            ],
            RequestKeys::BIO         => ['sometimes', 'nullable', 'string'],
            RequestKeys::ADDRESS     => ['sometimes', 'required', 'string'],
            RequestKeys::ADDRESS_2   => ['sometimes', 'nullable', 'string'],
            RequestKeys::COUNTRY     => ['sometimes', 'required', 'string', new InValidCountries()],
            RequestKeys::STATE       => [
                'sometimes',
                'required',
                'string',
                new InCountryStates($this->request->get(RequestKeys::COUNTRY)),
            ],
            RequestKeys::CITY        => ['sometimes', 'required', 'string'],
            RequestKeys::ZIP_CODE    => ['sometimes', 'required', 'string', 'bail', 'digits:5'],

            RequestKeys::HAT_REQUESTED => [
                'sometimes',
                'boolean',
            ],
        ];

        $newRules = $this->completeCompanyRules($this->request);

        return array_merge($rules, $newRules);
    }

    private function completeCompanyRules($request)
    {
        $newRules        = [];
        $companyRouteKey = $request->get(RequestKeys::COMPANY);
        if ($companyRouteKey) {
            $company        = Company::scoped(new ByRouteKey($companyRouteKey))->first();
            $validJobTitles = [];
            if ($company) {
                $companyType    = $company->type;
                $validJobTitles = CompanyDataType::getJobTitles($companyType);
            }

            $newRules = [
                RequestKeys::COMPANY                => [
                    'string',
                    Rule::exists(Company::class, 'uuid'),
                ],
                RequestKeys::JOB_TITLE              => [
                    'bail',
                    'required',
                    'string',
                    Rule::in($validJobTitles),
                ],
                RequestKeys::PRIMARY_EQUIPMENT_TYPE => [
                    'string',
                    Rule::in(CompanyDataType::ALL_EQUIPMENT_TYPES),
                ],
            ];
        }

        return $newRules;
    }

    public function messages()
    {
        return [
            RequestKeys::PUBLIC_NAME . '.regex' => 'The :attribute must start with a letter.',
        ];
    }
}

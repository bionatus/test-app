<?php

namespace App\Http\Requests\Api\V4\Account\Company;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Rules\InCountryStates;
use App\Rules\InValidCountries;
use App\Types\CompanyDataType;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        $companyType    = $this->get(RequestKeys::COMPANY_TYPE);
        $validJobTitles = is_string($companyType) ? CompanyDataType::getJobTitles($companyType) : [];

        return [
            RequestKeys::COMPANY_NAME           => [
                'required',
                'string',
            ],
            RequestKeys::COMPANY_TYPE           => [
                'required',
                'string',
                Rule::in(CompanyDataType::ALL_COMPANY_TYPES),
            ],
            RequestKeys::COMPANY_COUNTRY        => [
                'required',
                'string',
                new InValidCountries(),
            ],
            RequestKeys::COMPANY_STATE          => [
                'required',
                'bail',
                'string',
                new InCountryStates($this->get(RequestKeys::COMPANY_COUNTRY)),
            ],
            RequestKeys::COMPANY_CITY           => [
                'required',
                'string',
            ],
            RequestKeys::COMPANY_ZIP_CODE       => [
                'required',
                'string',
                'bail',
                'digits:5',
            ],
            RequestKeys::COMPANY_ADDRESS        => [
                'required',
                'string',
            ],
            RequestKeys::JOB_TITLE              => [
                'required',
                'string',
                Rule::in($validJobTitles),
            ],
            RequestKeys::PRIMARY_EQUIPMENT_TYPE => [
                'exclude_unless:' . RequestKeys::COMPANY_TYPE . ',' . CompanyDataType::TYPE_CONTRACTOR,
                'string',
                Rule::in(CompanyDataType::ALL_EQUIPMENT_TYPES),
            ],
        ];
    }
}

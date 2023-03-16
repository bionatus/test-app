<?php

namespace App\Http\Requests\Api\V4\Account\Company;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\Company;
use App\Models\Scopes\ByUuid;
use App\Types\CompanyDataType;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        $companyId = $this->get(RequestKeys::COMPANY);
        $company     = ($companyId) ? Company::scoped(new ByUuid($companyId))->first() : null;
        $companyType = $company ? $company->type : null;

        $rules = [
            RequestKeys::COMPANY => [
                'required',
                'string',
                Rule::exists(Company::tableName(), Company::routeKeyName()),
            ],

            RequestKeys::JOB_TITLE => [
                'required',
                'string',
                Rule::in(is_string($companyType) ? CompanyDataType::getJobTitles($companyType) : []),
            ],

            RequestKeys::PRIMARY_EQUIPMENT_TYPE => [
                'string',
                Rule::in(CompanyDataType::ALL_EQUIPMENT_TYPES),
            ],
        ];
        if ($companyType !== CompanyDataType::TYPE_CONTRACTOR) {
            $rules[RequestKeys::PRIMARY_EQUIPMENT_TYPE] = array_merge(['exclude'], $rules[RequestKeys::PRIMARY_EQUIPMENT_TYPE]);
        }

        return $rules;
    }
}

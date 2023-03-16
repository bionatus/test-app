<?php

namespace App\Http\Requests\Api\V4\Order;

use App\Constants\RequestKeys;
use App\Http\Requests\FormRequest;
use App\Models\Company;
use App\Models\Oem;
use App\Models\User;
use App\Rules\CompanyUser\Exists as CompanyUserExists;
use Auth;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        /** @var User $user */
        $user = Auth::user();

        $rules[RequestKeys::OEM] = [
            'nullable',
            'string',
            Rule::exists(Oem::tableName(), Oem::routeKeyName()),
        ];

        $rules[RequestKeys::COMPANY] = [
            'nullable',
            'string',
            'bail',
            new CompanyUserExists($user),
        ];

        return $rules;
    }

    public function oem(): ?Oem
    {
        $oem = $this->get(RequestKeys::OEM);
        if (empty($oem)) {
            return null;
        }

        return Oem::where(Oem::routeKeyName(), $oem)->first();
    }

    public function company(): ?Company
    {
        $companyId = $this->get(RequestKeys::COMPANY);
        if (empty($companyId)) {
            return null;
        }

        return Company::where(Company::routeKeyName(), $companyId)->first();
    }
}

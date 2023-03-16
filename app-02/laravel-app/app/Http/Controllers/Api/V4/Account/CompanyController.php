<?php

namespace App\Http\Controllers\Api\V4\Account;

use App\Constants\RequestKeys;
use App\Events\User\CompanyUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V4\Account\Company\StoreRequest;
use App\Http\Requests\Api\V4\Account\Company\UpdateRequest;
use App\Http\Resources\Api\V4\Account\Company\BaseResource;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Scopes\ByUuid;
use App\Models\User;
use Auth;

class CompanyController extends Controller
{
    public function store(StoreRequest $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $company           = new Company();
        $company->name     = $request->get(RequestKeys::COMPANY_NAME);
        $company->type     = $request->get(RequestKeys::COMPANY_TYPE);
        $company->country  = $request->get(RequestKeys::COMPANY_COUNTRY);
        $company->state    = $request->get(RequestKeys::COMPANY_STATE);
        $company->city     = $request->get(RequestKeys::COMPANY_CITY);
        $company->zip_code = $request->get(RequestKeys::COMPANY_ZIP_CODE);
        $company->address  = $request->get(RequestKeys::COMPANY_ADDRESS);
        $company->save();
        if (!($companyUser = $user->companyUser)) {
            $companyUser          = new CompanyUser();
            $companyUser->user_id = $user->getKey();
        }
        $companyUser->company_id     = $company->getKey();
        $companyUser->job_title      = $request->get(RequestKeys::JOB_TITLE);
        $companyUser->equipment_type = $request->get(RequestKeys::PRIMARY_EQUIPMENT_TYPE);
        $companyUser->save();
        $user->load('companyUser.company');
        CompanyUpdated::dispatch($companyUser);

        return new BaseResource($user);
    }

    public function update(UpdateRequest $request)
    {
        /** @var User $user */
        $user    = Auth::user();

        $company = Company::scoped(new ByUuid($request->get(RequestKeys::COMPANY)))->first();
        if (!($companyUser = $user->companyUser)) {
            $companyUser          = new CompanyUser();
            $companyUser->user_id = $user->getKey();
        }
        $companyUser->company_id     = $company->getKey();
        $companyUser->job_title      = $request->get(RequestKeys::JOB_TITLE);
        $companyUser->equipment_type = $request->get(RequestKeys::PRIMARY_EQUIPMENT_TYPE);
        $companyUser->save();
        
        $user->load('companyUser.company');
        CompanyUpdated::dispatch($companyUser);

        return new BaseResource($user);
    }
}

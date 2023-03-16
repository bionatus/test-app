<?php

namespace App\Http\Controllers\Api\Nova;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Nova\JobTitle\IndexRequest;
use App\Http\Resources\Api\Nova\JobTitle\JobTitleResource;
use App\Types\CompanyDataType;

class JobTitleController extends Controller
{
    public function index(IndexRequest $request)
    {
        $companyType = $request->get(RequestKeys::COMPANY_TYPE);

        $jobTitles = CompanyDataType::getJobTitles($companyType);

        JobTitleResource::withoutWrapping();

        return JobTitleResource::collection($jobTitles);
    }
}

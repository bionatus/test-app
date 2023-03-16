<?php

namespace App\Http\Controllers\Api\V4;

use App;
use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V4\Company\IndexRequest;
use App\Http\Resources\Api\V4\Company\BaseResource;
use App\Models\Company;
use App\Models\Scopes\Alphabetically;
use App\Models\Scopes\BySearchString;

class CompanyController extends Controller
{
    public function index(IndexRequest $request)
    {
        $query = Company::query()
            ->scoped(new BySearchString($request->get(RequestKeys::SEARCH_STRING)))
            ->scoped(new Alphabetically());

        $page = $query->paginate();

        return BaseResource::collection($page);
    }
}

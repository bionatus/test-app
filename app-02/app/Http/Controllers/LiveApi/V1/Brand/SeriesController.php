<?php

namespace App\Http\Controllers\LiveApi\V1\Brand;

use App\Constants\RequestKeys;
use App\Http\Requests\LiveApi\V1\Brand\Series\IndexRequest;
use App\Http\Resources\LiveApi\V1\Brand\Series\BaseResource;
use App\Models\Brand;
use App\Models\Scopes\Alphabetically;
use App\Models\Scopes\BySearchString;
use App\Models\Series\Scopes\Active;
use App\Models\Staff;
use Auth;

class SeriesController
{
    public function index(IndexRequest $request, Brand $brand)
    {
        /** @var Staff $staff */
        $staff   = Auth::user();
        $brand->brandDetailCounters()->create(['staff_id' => $staff->getKey()]);

        $query = $brand->series()
            ->scoped(new Active())
            ->scoped(new BySearchString($request->get(RequestKeys::SEARCH_STRING)))
            ->scoped(new Alphabetically());

        $page = $query->paginate();

        return BaseResource::collection($page);
    }
}

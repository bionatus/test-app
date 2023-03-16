<?php

namespace App\Http\Controllers\Api\V3\Brand;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Brand\Series\IndexRequest;
use App\Http\Resources\Api\V3\Brand\Series\BaseResource;
use App\Models\Brand;
use App\Models\Scopes\Alphabetically;
use App\Models\Scopes\BySearchString;
use App\Models\Series\Scopes\Active;
use Auth;

class SeriesController extends Controller
{
    public function index(IndexRequest $request, Brand $brand)
    {
        $brand->brandDetailCounters()->create(['user_id' => Auth::id()]);

        $query = $brand->series()
            ->scoped(new Active())
            ->scoped(new BySearchString($request->get(RequestKeys::SEARCH_STRING)))
            ->scoped(new Alphabetically());

        $page = $query->paginate();

        return BaseResource::collection($page);
    }
}

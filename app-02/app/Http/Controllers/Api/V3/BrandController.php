<?php

namespace App\Http\Controllers\Api\V3;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Brand\IndexRequest;
use App\Http\Resources\Api\V3\Brand\BaseResource;
use App\Models\Brand;
use App\Models\Scopes\Alphabetically;
use App\Models\Scopes\BySearchString;
use App\Models\Scopes\Published;

class BrandController extends Controller
{
    public function index(IndexRequest $request)
    {
        $query = Brand::query()
            ->withCount('series')
            ->scoped(new Published())
            ->scoped(new BySearchString($request->get(RequestKeys::SEARCH_STRING)))
            ->scoped(new Alphabetically());

        $page = $query->paginate();

        return BaseResource::collection($page);
    }
}

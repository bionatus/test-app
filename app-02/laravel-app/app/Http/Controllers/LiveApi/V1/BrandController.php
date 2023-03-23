<?php

namespace App\Http\Controllers\LiveApi\V1;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V1\Brand\IndexRequest;
use App\Http\Resources\LiveApi\V1\Brand\BaseResource;
use App\Models\Brand;
use App\Models\Scopes\Alphabetically;
use App\Models\Scopes\BySearchString;

class BrandController extends Controller
{
    public function index(IndexRequest $request)
    {
        $brands = Brand::query()
            ->scoped(new BySearchString($request->get(RequestKeys::SEARCH_STRING)))
            ->scoped(new Alphabetically())
            ->paginate(1000);

        return BaseResource::collection($brands);
    }
}

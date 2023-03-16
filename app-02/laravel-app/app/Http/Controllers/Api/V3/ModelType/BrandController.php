<?php

namespace App\Http\Controllers\Api\V3\ModelType;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\ModelType\Brand\IndexRequest;
use App\Http\Resources\Api\V3\ModelType\Brand\BaseResource;
use App\Models\Brand;
use App\Models\Brand\Scopes\ByModelType;
use App\Models\ModelType;
use App\Models\Scopes\Alphabetically;
use App\Models\Scopes\BySearchString;
use App\Models\Scopes\Published;

class BrandController extends Controller
{
    public function index(ModelType $modelType, IndexRequest $request)
    {
        $query = Brand::query()
            ->scoped(new ByModelType($modelType))
            ->scoped(new Published())
            ->scoped(new BySearchString($request->get(RequestKeys::SEARCH_STRING)))
            ->scoped(new Alphabetically());

        $page = $query->paginate();

        return BaseResource::collection($page);
    }
}

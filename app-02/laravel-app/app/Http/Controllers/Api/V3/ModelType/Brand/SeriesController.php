<?php

namespace App\Http\Controllers\Api\V3\ModelType\Brand;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V3\ModelType\Brand\Series\IndexRequest;
use App\Http\Resources\Api\V3\ModelType\Brand\Series\BaseResource;
use App\Models\Brand;
use App\Models\ModelType;
use App\Models\Scopes\Alphabetically;
use App\Models\Scopes\BySearchString;
use App\Models\Series\Scopes\Active;
use App\Models\Series\Scopes\ByOemType;

class SeriesController
{
    public function index(ModelType $modelType, IndexRequest $request, Brand $brand)
    {
        $query = $brand->series()
            ->scoped(new ByOemType($modelType))
            ->scoped(new Active())
            ->scoped(new BySearchString($request->get(RequestKeys::SEARCH_STRING)))
            ->scoped(new Alphabetically());

        $page = $query->paginate();

        return BaseResource::collection($page);
    }
}

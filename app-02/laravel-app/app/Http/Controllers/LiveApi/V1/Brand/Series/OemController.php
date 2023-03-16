<?php

namespace App\Http\Controllers\LiveApi\V1\Brand\Series;

use App\Constants\RequestKeys;
use App\Http\Requests\LiveApi\V1\Brand\Series\Oem\IndexRequest;
use App\Http\Resources\LiveApi\V1\Brand\Series\Oem\BaseResource;
use App\Models\Brand;
use App\Models\Oem\Scopes\Alphabetically;
use App\Models\Oem\Scopes\ByModel;
use App\Models\Oem\Scopes\Live;
use App\Models\Series;

class OemController
{
    /** @noinspection PhpUnusedParameterInspection */
    public function index(IndexRequest $request, Brand $brand, Series $series)
    {
        $page = $series->oems()
            ->scoped(new ByModel($request->get(RequestKeys::SEARCH_STRING)))
            ->scoped(new Alphabetically('model'))
            ->scoped(new Alphabetically('model_notes'))
            ->scoped(new Live())
            ->paginate();

        return BaseResource::collection($page);
    }
}

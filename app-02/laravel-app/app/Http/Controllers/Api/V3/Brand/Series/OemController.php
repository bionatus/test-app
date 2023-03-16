<?php

namespace App\Http\Controllers\Api\V3\Brand\Series;

use App\Constants\RequestKeys;
use App\Http\Requests\Api\V3\Brand\Series\Oem\IndexRequest;
use App\Http\Resources\Api\V3\Brand\Series\Oem\BaseResource;
use App\Models\Brand;
use App\Models\Oem\Scopes\Live;
use App\Models\Scopes\Alphabetically;
use App\Models\Scopes\BySearchString;
use App\Models\Series;

class OemController
{
    const SCOPE_MODEL       = 'model';
    const SCOPE_MODEL_NOTES = 'model_notes';

    /** @noinspection PhpUnusedParameterInspection */
    public function index(IndexRequest $request, Brand $brand, Series $series)
    {
        $query = $series->oems();
        $query->scoped(new Alphabetically(self::SCOPE_MODEL))
            ->scoped(new Alphabetically(self::SCOPE_MODEL_NOTES))
            ->scoped(new Live())
            ->scoped(new BySearchString($request->get(RequestKeys::SEARCH_STRING), self::SCOPE_MODEL));

        $page = $query->paginate();

        return BaseResource::collection($page);
    }
}

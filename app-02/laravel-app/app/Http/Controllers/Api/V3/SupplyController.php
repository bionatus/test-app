<?php

namespace App\Http\Controllers\Api\V3;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Supply\IndexRequest;
use App\Http\Resources\Api\V3\Supply\BaseResource;
use App\Models\Scopes\Alphabetically;
use App\Models\Scopes\AlphabeticallyWithNullLast;
use App\Models\Scopes\ByRouteKey;
use App\Models\Scopes\BySearchString;
use App\Models\Scopes\Visible;
use App\Models\SupplyCategory;

class SupplyController extends Controller
{
    public function index(IndexRequest $request)
    {
        $supplyCategorySlug = $request->get(RequestKeys::SUPPLY_CATEGORY);
        /** @var SupplyCategory $supplyCategory */
        $supplyCategory = SupplyCategory::scoped(new ByRouteKey($supplyCategorySlug))->first();

        $query = $supplyCategory->supplies()->with('item');

        if ($request->get(RequestKeys::SEARCH_STRING)) {
            $query = $query->scoped(new BySearchString($request->get(RequestKeys::SEARCH_STRING)));
        }

        $supplies = $query->scoped(new Visible())
            ->scoped(new AlphabeticallyWithNullLast('sort'))
            ->scoped(new Alphabetically('name'))
            ->paginate();

        return BaseResource::collection($supplies);
    }
}

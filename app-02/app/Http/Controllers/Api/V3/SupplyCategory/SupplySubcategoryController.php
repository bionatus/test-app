<?php

namespace App\Http\Controllers\Api\V3\SupplyCategory;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\SupplyCategory\SupplySubcategory\IndexRequest;
use App\Http\Resources\Api\V3\SupplyCategory\BaseResource;
use App\Models\Scopes\Alphabetically;
use App\Models\Scopes\AlphabeticallyWithNullLast;
use App\Models\Scopes\BySearchString;
use App\Models\Scopes\Visible;
use App\Models\SupplyCategory;
use Auth;

class SupplySubcategoryController extends Controller
{
    public function index(IndexRequest $request, SupplyCategory $supplyCategory)
    {
        $user = Auth::user();
        
        $subcategoriesQuery = $supplyCategory->children();
        if ($request->get(RequestKeys::SEARCH_STRING)) {
            $subcategoriesQuery->scoped(new BySearchString($request->get(RequestKeys::SEARCH_STRING)));
        }

        $subcategories = $subcategoriesQuery->scoped(new AlphabeticallyWithNullLast('sort'))
            ->scoped(new Alphabetically('name'))
            ->scoped(new Visible())
            ->paginate();

        if ($subcategories->onFirstPage()) {
            $supplyCategory->supplyCategoryViews()->create(['user_id' => $user->getRouteKey()]);
        }

        return BaseResource::collection($subcategories);
    }
}

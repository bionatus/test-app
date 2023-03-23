<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V3\SupplyCategory\BaseResource;
use App\Models\Scopes\Alphabetically;
use App\Models\Scopes\AlphabeticallyWithNullLast;
use App\Models\Scopes\ByParent;
use App\Models\Scopes\Visible;
use App\Models\SupplyCategory;

class SupplyCategoryController extends Controller
{
    public function index()
    {
        $categories = SupplyCategory::with('children')
            ->scoped(new ByParent())
            ->scoped(new AlphabeticallyWithNullLast('sort'))
            ->scoped(new Alphabetically('name'))
            ->scoped(new Visible())
            ->paginate();

        return BaseResource::collection($categories);
    }
}

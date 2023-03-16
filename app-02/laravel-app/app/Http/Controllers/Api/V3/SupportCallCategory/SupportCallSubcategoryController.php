<?php

namespace App\Http\Controllers\Api\V3\SupportCallCategory;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V3\SupportCallCategory\SupportCallSubcategory\BaseResource;
use App\Models\Scopes\Alphabetically;
use App\Models\Scopes\AlphabeticallyWithNullLast;
use App\Models\SupportCallCategory;

class SupportCallSubcategoryController extends Controller
{
    public function index(SupportCallCategory $supportCallCategory)
    {
        $subcategories = $supportCallCategory->children()
            ->with('instruments')
            ->scoped(new AlphabeticallyWithNullLast('sort'))
            ->scoped(new Alphabetically('name'))
            ->paginate();

        return BaseResource::collection($subcategories);
    }
}

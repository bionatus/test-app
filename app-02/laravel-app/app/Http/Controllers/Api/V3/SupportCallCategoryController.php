<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V3\SupportCallCategory\BaseResource;
use App\Models\Scopes\Alphabetically;
use App\Models\Scopes\AlphabeticallyWithNullLast;
use App\Models\Scopes\ByParent;
use App\Models\SupportCallCategory;

class SupportCallCategoryController extends Controller
{
    public function index()
    {
        $categories = SupportCallCategory::with('instruments')
            ->withExists('children')
            ->scoped(new ByParent())
            ->scoped(new AlphabeticallyWithNullLast('sort'))
            ->scoped(new Alphabetically('name'))
            ->paginate();

        return BaseResource::collection($categories);
    }
}

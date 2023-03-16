<?php

namespace App\Http\Controllers\Api\V3\Brand;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V3\Brand\MostSearched\BaseResource;
use App\Models\Brand;
use App\Models\Brand\Scopes\MostSearched;
use App\Models\Scopes\Alphabetically;

class MostSearchedController extends Controller
{
    public function __invoke()
    {
        $brands = Brand::scoped(new MostSearched())->scoped(new Alphabetically())->paginate();

        return BaseResource::collection($brands);
    }
}

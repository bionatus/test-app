<?php

namespace App\Http\Controllers\LiveApi\V1\Oem;

use App\Http\Controllers\Controller;

use App\Http\Resources\LiveApi\V1\Oem\Part\BaseResource;
use App\Models\Oem;
use App\Models\Part\Scopes\Alphabetically;
use App\Models\Part\Scopes\FunctionalFirst;
use App\Models\Part\Scopes\Number;

class PartController extends Controller
{
    public function index(Oem $oem)
    {
        $parts = $oem->parts()
            ->with('item')
            ->scoped(new FunctionalFirst())
            ->scoped(new Alphabetically())
            ->scoped(new Number())
            ->paginate();

        return BaseResource::collection($parts);
    }
}

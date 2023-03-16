<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V3\ModelType\BaseResource;
use App\Models\ModelType;
use App\Models\Scopes\AlphabeticallyWithNullLast;

class ModelTypeController extends Controller
{
    public function index()
    {
        $types = ModelType::scoped(new AlphabeticallyWithNullLast('sort'))->get();

        return BaseResource::collection($types);
    }
}

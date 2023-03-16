<?php

namespace App\Http\Controllers\LiveApi\V1\Supplier;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V1\Supplier\BulkBrand\StoreRequest;
use App\Http\Resources\LiveApi\V1\Supplier\BulkBrand\BaseResource;
use App\Models\Brand;
use App\Models\Scopes\ByRouteKeys;
use Auth;
use Symfony\Component\HttpFoundation\Response;

class BulkBrandController extends Controller
{
    public function store(StoreRequest $request)
    {
        $supplier = Auth::user()->supplier;

        $brandKeys = (array) $request->get(RequestKeys::BRANDS);
        $brands    = Brand::scoped(new ByRouteKeys($brandKeys));

        $supplier->brands()->sync($brands->pluck(Brand::keyName()));

        return BaseResource::collection($supplier->brands)->response()->setStatusCode(Response::HTTP_CREATED);
    }
}

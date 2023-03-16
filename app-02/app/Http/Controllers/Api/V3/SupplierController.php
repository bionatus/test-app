<?php

namespace App\Http\Controllers\Api\V3;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Supplier\IndexRequest;
use App\Http\Resources\Api\V3\Supplier\BaseResource;
use App\Http\Resources\Api\V3\Supplier\DetailedResource;
use App\Models\Scopes\Alphabetically;
use App\Models\Scopes\ByKey;
use App\Models\Scopes\BySearchString;
use App\Models\Supplier;
use App\Models\Supplier\Scopes\NearToCoordinates;
use App\Models\Supplier\Scopes\NearZipCodes;
use App\Models\Supplier\Scopes\Preferred;
use Auth;

class SupplierController extends Controller
{
    public function index(IndexRequest $request)
    {
        $query = Supplier::query();
        $query->scoped(new BySearchString($request->get(RequestKeys::SEARCH_STRING)));
        $user = Auth::user();

        $query->scoped(new Preferred($user));

        if ($zipCode = $request->get(RequestKeys::ZIP_CODE)) {
            $query->scoped(new NearZipCodes($zipCode));
        }

        if (($company = $user->company()->first()) && $company->hasValidZipCode() && $company->hasValidCoordinates()) {
            $query->scoped(new NearToCoordinates($company->latitude, $company->longitude));
        }

        $query->scoped(new Alphabetically());

        $page = $query->paginate();
        $page->appends($request->validated());

        return BaseResource::collection($page);
    }

    public function show(Supplier $supplier)
    {
        $user  = Auth::user();
        $query = Supplier::query();
        $query->scoped(new ByKey($supplier->getKey()));

        if (($company = $user->company()->first()) && $company->hasValidZipCode() && $company->hasValidCoordinates()) {
            $query->scoped(new NearToCoordinates($company->latitude, $company->longitude));
        }

        $supplierWithDistance = $query->first();

        return new DetailedResource($supplierWithDistance);
    }
}

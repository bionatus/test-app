<?php

namespace App\Http\Controllers\LiveApi\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\LiveApi\V1\LimitedSupplier\BaseResource;
use Auth;

use Symfony\Component\HttpFoundation\JsonResponse;

class LimitedSupplierController extends Controller
{
    public function show(): JsonResponse
    {
        $supplier = Auth::user()->supplier;

        return (new BaseResource($supplier))->response();
    }
}

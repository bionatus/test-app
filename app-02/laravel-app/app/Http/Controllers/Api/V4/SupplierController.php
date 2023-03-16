<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V4\Supplier\DetailedResource;
use App\Models\Supplier;

class SupplierController extends Controller
{
    public function show(Supplier $supplier)
    {
        return new DetailedResource($supplier);
    }
}

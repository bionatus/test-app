<?php

namespace App\Http\Controllers\LiveApi\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\LiveApi\V1\User\BaseResource;
use App\Models\Scopes\ByStatus;
use App\Models\SupplierUser;
use Auth;

class UserController extends Controller
{
    public function index(): BaseResource
    {
        $supplier = Auth::user()->supplier;

        $confirmedUsers   = $supplier->supplierUsers()->scoped(new ByStatus(SupplierUser::STATUS_CONFIRMED))->get();
        $unconfirmedUsers = $supplier->supplierUsers()->scoped(new ByStatus(SupplierUser::STATUS_UNCONFIRMED))->get();

        return new BaseResource($confirmedUsers, $unconfirmedUsers);
    }
}

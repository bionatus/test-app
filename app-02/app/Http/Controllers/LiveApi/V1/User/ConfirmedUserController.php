<?php

namespace App\Http\Controllers\LiveApi\V1\User;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V1\User\SupplierUser\StoreRequest;
use App\Http\Resources\LiveApi\V1\User\SupplierUserResource;
use App\Models\Scopes\ByUser;
use App\Models\SupplierUser;
use App\Models\User;
use Auth;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ConfirmedUserController extends Controller
{
    public function store(StoreRequest $request, User $user)
    {
        $supplier = Auth::user()->supplier;

        /** @var SupplierUser $supplierUser */
        $supplierUser = $supplier->supplierUsers()->scoped(new ByUser($user))->first();

        $supplierUser->status        = SupplierUser::STATUS_CONFIRMED;
        $supplierUser->customer_tier = $request->get(RequestKeys::CUSTOMER_TIER);
        $supplierUser->cash_buyer    = $request->get(RequestKeys::CASH_BUYER);
        $supplierUser->save();

        return (new SupplierUserResource($supplierUser))->response()->setStatusCode(HttpResponse::HTTP_CREATED);
    }
}

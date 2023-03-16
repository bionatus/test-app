<?php

namespace App\Http\Controllers\LiveApi\V1;

use App\Constants\RequestKeys;
use App\Events\User\ConfirmedBySupplier;
use App\Events\User\UnconfirmedBySupplier;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V1\ConfirmedUser\ConfirmRequest;
use App\Http\Requests\LiveApi\V1\ConfirmedUser\UpdateRequest;
use App\Http\Resources\LiveApi\V1\User\ExtendedSupplierUserResource;
use App\Models\Scopes\ByUser;
use App\Models\SupplierUser;
use App\Models\User;
use Auth;
use Symfony\Component\HttpFoundation\Response;

class ConfirmedUserController extends Controller
{
    public function confirm(ConfirmRequest $request, User $user)
    {
        $supplier = Auth::user()->supplier;

        /** @var SupplierUser $supplierUser */
        $supplierUser = $supplier->supplierUsers()->scoped(new ByUser($user))->first();

        $supplierUser->status        = SupplierUser::STATUS_CONFIRMED;
        $supplierUser->cash_buyer    = $request->get(RequestKeys::CASH_BUYER);
        $supplierUser->customer_tier = $request->get(RequestKeys::CUSTOMER_TIER);
        $supplierUser->save();

        ConfirmedBySupplier::dispatch($supplier);

        return (new ExtendedSupplierUserResource($supplierUser))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function delete(User $user)
    {
        $supplier = Auth::user()->supplier;

        /** @var SupplierUser $supplierUser */
        $supplierUser = $supplier->supplierUsers()->scoped(new ByUser($user))->first();

        $supplierUser->status        = SupplierUser::STATUS_UNCONFIRMED;
        $supplierUser->cash_buyer    = false;
        $supplierUser->customer_tier = null;
        $supplierUser->save();

        UnconfirmedBySupplier::dispatch($supplier);

        return new ExtendedSupplierUserResource($supplierUser);
    }

    public function update(UpdateRequest $request, User $user)
    {
        $supplier = Auth::user()->supplier;

        /** @var SupplierUser $supplierUser */
        $supplierUser = $supplier->supplierUsers()->scoped(new ByUser($user))->first();

        $supplierUser->cash_buyer    = $request->get(RequestKeys::CASH_BUYER);
        $supplierUser->customer_tier = $request->get(RequestKeys::CUSTOMER_TIER);
        $supplierUser->save();

        return new ExtendedSupplierUserResource($supplierUser);
    }
}

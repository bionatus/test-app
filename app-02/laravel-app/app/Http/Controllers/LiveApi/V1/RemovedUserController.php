<?php

namespace App\Http\Controllers\LiveApi\V1;

use App\Events\User\RemovedBySupplier;
use App\Events\User\RestoredBySupplier;
use App\Http\Controllers\Controller;
use App\Http\Resources\LiveApi\V1\User\ExtendedSupplierUserResource;
use App\Models\Scopes\ByStatus;
use App\Models\Scopes\ByUser;
use App\Models\SupplierUser;
use App\Models\User;
use Auth;
use Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class RemovedUserController extends Controller
{
    public function index()
    {
        $supplier = Auth::user()->supplier;

        /** @var SupplierUser $removedUsers */
        $removedUsers = $supplier->supplierUsers()->scoped(new ByStatus(SupplierUser::STATUS_REMOVED))->paginate();

        return ExtendedSupplierUserResource::collection($removedUsers);
    }

    public function store(User $user)
    {
        $supplier = Auth::user()->supplier;

        /** @var SupplierUser $supplierUser */
        $supplierUser = $supplier->supplierUsers()->scoped(new ByUser($user))->first();

        $supplierUser->status = SupplierUser::STATUS_REMOVED;
        $supplierUser->save();

        RemovedBySupplier::dispatch($supplier);

        return (new ExtendedSupplierUserResource($supplierUser))->response()->setStatusCode(HttpResponse::HTTP_CREATED);
    }

    public function delete(User $user)
    {
        $supplier = Auth::user()->supplier;

        $supplierUser = $supplier->supplierUsers()->scoped(new ByUser($user))->first();

        $supplierUser->status = SupplierUser::STATUS_UNCONFIRMED;
        $supplierUser->save();

        RestoredBySupplier::dispatch($supplier);

        return Response::noContent();
    }
}

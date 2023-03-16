<?php

namespace App\Http\Controllers\Api\V3\Account;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Account\Supplier\StoreRequest;
use App\Http\Resources\Api\V3\Account\Supplier\BaseResource;
use App\Models\Scopes\ByUuid;
use App\Models\Supplier;
use App\Models\Supplier\Scopes\Preferred;
use Auth;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class SupplierController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return BaseResource::collection($user->visibleSuppliers()->scoped(new Preferred($user))->paginate());
    }

    public function store(StoreRequest $request)
    {
        $user = Auth::user();

        /** @var Supplier $supplier */
        $supplier = Supplier::scoped(new ByUuid($request->get(RequestKeys::SUPPLIER)))->first();
        $user->suppliers()->syncWithPivotValues($supplier, ['visible_by_user' => true], false);

        return (new BaseResource($supplier))->response()->setStatusCode(HttpResponse::HTTP_CREATED);
    }
}

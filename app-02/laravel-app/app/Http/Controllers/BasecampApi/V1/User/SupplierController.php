<?php

namespace App\Http\Controllers\BasecampApi\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\BasecampApi\V1\User\Supplier\BaseResource;
use App\Models\Scopes\ByStatus;
use App\Models\SupplierUser;
use App\Models\User;
use Request;

class SupplierController extends Controller
{
    public function __invoke(Request $request, User $user)
    {
        $suppliers = $user->visibleSuppliers()->scoped(new ByStatus(SupplierUser::STATUS_CONFIRMED))->paginate();

        return BaseResource::collection($suppliers);
    }
}

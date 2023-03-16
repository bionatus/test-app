<?php

namespace App\Http\Controllers\Api\V3\Account;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V3\Account\VerifiedSupplier\BaseResource;
use App\Models\Supplier\Scopes\ByPublished;
use App\Models\Supplier\Scopes\Verified;
use Auth;

class VerifiedSupplierController extends Controller
{
    public function __invoke()
    {
        $user = Auth::user();

        $suppliersCount = $user->visibleSuppliers()->scoped(new Verified())->scoped(new ByPublished())->count();

        return new BaseResource($suppliersCount);
    }
}

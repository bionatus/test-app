<?php

namespace App\Http\Controllers\Api\V3\Account;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V3\Account\Supplier\BriefResource;
use App\Models\Supplier\Scopes\NearToCoordinates;
use App\Models\Supplier\Scopes\Preferred;
use Auth;

class BriefSupplierController extends Controller
{
    public function __invoke()
    {
        $user         = Auth::user();
        $userSupplier = $user->visibleSuppliers()->scoped(new Preferred($user));
        $company      = $user->company()->first();

        if ($company && $company->hasValidZipCode() && $company->hasValidCoordinates()) {
            $userSupplier->scoped(new NearToCoordinates($company->latitude, $company->longitude));
        }

        return BriefResource::collection($userSupplier->paginate());
    }
}

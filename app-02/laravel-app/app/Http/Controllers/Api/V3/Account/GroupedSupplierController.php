<?php

namespace App\Http\Controllers\Api\V3\Account;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V3\Account\Supplier\GroupedResource;
use App\Models\Scopes\Alphabetically;
use App\Models\Supplier;
use App\Models\Supplier\Scopes\ByOnTheNetwork;
use App\Models\Supplier\Scopes\NearToCoordinates;
use App\Models\Supplier\Scopes\Preferred;
use App\Models\Supplier\Scopes\PublishedFavorite;
use App\Models\User;
use Auth;

class GroupedSupplierController extends Controller
{
    public function index()
    {
        $query = Supplier::query();

        /** @var User $user */
        $user = Auth::user();

        $query->scoped(new ByOnTheNetwork())->scoped(new Preferred($user))->scoped(new PublishedFavorite($user));

        if (($company = $user->company()->first()) && $company->hasValidZipCode() && $company->hasValidCoordinates()) {
            $query->scoped(new NearToCoordinates($company->latitude, $company->longitude));
        }

        $query->scoped(new Alphabetically());
        $page = $query->paginate();

        return GroupedResource::collection($page);
    }
}

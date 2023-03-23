<?php

namespace App\Http\Controllers\Api\V3;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\OrderSupplier\IndexRequest;
use App\Http\Resources\Api\V3\OrderSupplier\BaseResource;
use App\Models\Scopes\Alphabetically;
use App\Models\Supplier;
use App\Models\Supplier\Scopes\BySearchString;
use App\Models\Supplier\Scopes\InvitationSent;
use App\Models\Supplier\Scopes\NearToCoordinates;
use App\Models\Supplier\Scopes\Preferred;
use App\Models\Supplier\Scopes\PublishedFavorite;
use App\Types\Location;
use Auth;

class OrderSupplierController extends Controller
{
    public function index(IndexRequest $request)
    {
        $user = Auth::user();

        $query = Supplier::query()->with('supplierHours');

        $query->with(['media'])
            ->scoped(new InvitationSent($user))
            ->scoped(new Preferred($user))
            ->scoped(new PublishedFavorite($user));

        if ($searchString = $request->get(RequestKeys::SEARCH_STRING)) {
            $query->scoped(new BySearchString($searchString));
        }

        if ($locationString = $request->get(RequestKeys::LOCATION)) {
            $location = Location::createFromString($locationString);
            $query->scoped(new NearToCoordinates($location->latitude(), $location->longitude()));
        }

        if (!$locationString && ($company = $user->company()
                ->first()) && $company->hasValidZipCode() && $company->hasValidCoordinates()) {
            $query->scoped(new NearToCoordinates($company->latitude, $company->longitude));
        }

        $query->scoped(new Alphabetically());
        $page = $query->paginate();

        if ($page->onFirstPage()) {
            $user->supplierListViews()->create();
        }

        return BaseResource::collection($page);
    }
}

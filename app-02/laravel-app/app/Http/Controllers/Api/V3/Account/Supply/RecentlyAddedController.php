<?php

namespace App\Http\Controllers\Api\V3\Account\Supply;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V3\Account\Supply\RecentlyAdded\BaseResource;
use App\Models\Scopes\ByUser;
use App\Models\Supply;
use App\Models\Supply\Scopes\LastAddedToCart;
use Auth;

class RecentlyAddedController extends Controller
{
    public function __invoke()
    {
        $user     = Auth::user();
        $supplies = Supply::with('supplyCategory')
            ->scoped(new ByUser($user))
            ->scoped(new LastAddedToCart())
            ->paginate();

        return BaseResource::collection($supplies);
    }
}

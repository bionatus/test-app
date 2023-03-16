<?php

namespace App\Http\Controllers\Api\V3\Account\Part;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V3\Account\Part\RecentlyViewed\BaseResource;
use App\Models\Part;
use App\Models\Part\Scopes\LastViewed;
use App\Models\Scopes\ByUser;
use Auth;

class RecentlyViewedController extends Controller
{
    public function __invoke()
    {
        $user  = Auth::user();
        $parts = Part::scoped(new ByUser($user))->scoped(new LastViewed())->paginate();

        return BaseResource::collection($parts);
    }
}

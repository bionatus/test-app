<?php

namespace App\Http\Controllers\Api\V3\Account\Oem;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V3\Account\Oem\RecentlyViewed\BaseResource;
use App\Models\Oem;
use App\Models\Oem\Scopes\LastViewed;
use App\Models\Scopes\ByUserId;
use Auth;

class RecentlyViewedController extends Controller
{
    public function __invoke()
    {
        $user = Auth::user();
        $page = Oem::scoped(new ByUserId($user->getKey()))->scoped(new LastViewed())->groupBy('user_id')->paginate();

        return BaseResource::collection($page);
    }
}

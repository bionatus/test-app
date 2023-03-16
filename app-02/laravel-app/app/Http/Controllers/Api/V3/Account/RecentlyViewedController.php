<?php

namespace App\Http\Controllers\Api\V3\Account;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V3\Account\RecentlyViewed\BaseResource;
use App\Types\RecentlyViewed;
use Auth;

class RecentlyViewedController extends Controller
{
    public function __invoke()
    {
        $user = Auth::user();

        $page = RecentlyViewed::query($user->getKey())->paginate();

        return BaseResource::collection($page);
    }
}

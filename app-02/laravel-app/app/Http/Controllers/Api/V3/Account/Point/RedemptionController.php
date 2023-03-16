<?php

namespace App\Http\Controllers\Api\V3\Account\Point;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V3\Account\Point\Redemption\BaseResource;
use App\Models\Point;
use App\Models\Point\Scopes\ByAction;
use App\Models\Scopes\ByUser;
use App\Models\Scopes\Newest;
use App\Models\XoxoRedemption;
use Auth;
use Illuminate\Database\Eloquent\Builder;

class RedemptionController extends Controller
{
    public function index()
    {
        $redemptions = XoxoRedemption::whereHas('point', function(Builder $query) {
            return $query->scoped(new ByUser(Auth::user()))->scoped(new ByAction(Point::ACTION_REDEEMED));
        })->scoped(new Newest())->paginate();

        return BaseResource::collection($redemptions);
    }
}

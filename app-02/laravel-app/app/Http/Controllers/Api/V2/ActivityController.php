<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V2\Activity\BaseResource;
use App\Models\Activity;
use App\Models\Activity\Scopes\ByCauser;
use App\Models\Activity\Scopes\ByUserRelatedActivity;
use App\Models\Activity\Scopes\Latest;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Activity::with('relatedActivity');
        $query->scoped(new ByCauser($user));
        $query->inLog(Activity::TYPE_FORUM);
        $query->orWhere(function(Builder $builder) use ($user) {
            $builder->scoped(new ByUserRelatedActivity($user));
        });
        $query->scoped(new Latest());
        $page = $query->paginate();
        $page->appends($request->all());

        $page->through(function(Activity $activity) use ($user) {
            if ($activity->causer_id != $user->getKey()) {
                $activity->event       = $activity->relatedActivity->first()->event;
                $activity->resource    = $activity->relatedActivity->first()->resource;
                $activity->description = $activity->resource . '.' . $activity->event;
            }

            return $activity;
        });

        return BaseResource::collection($page);
    }
}

<?php

namespace App\Http\Controllers\Api\V3;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Activity\IndexRequest;
use App\Http\Resources\Api\V2\Activity\BaseResource;
use App\Models\Activity;
use App\Models\Activity\Scopes\ByCauser;
use App\Models\Activity\Scopes\ByUserRelatedActivity;
use App\Models\Activity\Scopes\Latest;
use App\Models\User;
use Auth;
use Illuminate\Database\Eloquent\Builder;

class ActivityController extends Controller
{
    public function index(IndexRequest $request)
    {
        $user = Auth::user();

        $query = Activity::with('relatedActivity');
        $query->where(function($query) use ($user) {
            $query->scoped(new ByCauser($user));
            $query->orWhere(function(Builder $builder) use ($user) {
                /** @var User $user */
                $builder->scoped(new ByUserRelatedActivity($user));
            });
        });

        $logName = $request->get(RequestKeys::LOG_NAME);

        if (null === $logName) {
            $logName = Activity::TYPE_ALL;
        }

        $query->inLog($logName);
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

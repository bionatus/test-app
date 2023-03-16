<?php

namespace App\Http\Controllers\Api\V3;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\InternalNotification\IndexRequest;
use App\Http\Resources\Api\V3\InternalNotification\BaseResource;
use App\Models\AppNotification\Scopes\Unread as UnreadAppNotification;
use App\Models\InternalNotification;
use App\Models\InternalNotification\Scopes\Unread as UnreadInternalNotification;
use App\Models\Scopes\Latest;
use App\Notifications\UnreadNotificationCountUpdatedNotification;
use Auth;
use Illuminate\Support\Carbon;

class InternalNotificationController extends Controller
{
    public function index(IndexRequest $request)
    {
        $user = Auth::user();

        $internalNotifications = $user->internalNotifications()->scoped(new Latest())->paginate();

        $read = $request->get(RequestKeys::READ) ?? true;

        if ($read) {
            $user->internalNotifications()->scoped(new UnreadInternalNotification())->update([
                'read_at' => Carbon::now(),
            ]);
            $user->appNotifications()->scoped(new UnreadAppNotification())->update([
                'read' => true,
            ]);
            $user->notify(new UnreadNotificationCountUpdatedNotification(0));
        }

        return BaseResource::collection($internalNotifications);
    }

    public function show(InternalNotification $internalNotification)
    {
        if (!$internalNotification->isRead()) {
            $internalNotification->read();
        }

        return new BaseResource($internalNotification);
    }
}

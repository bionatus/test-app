<?php

namespace App\Http\Controllers\Api\V3\InternalNotification;

use App;
use App\Http\Controllers\Controller;
use App\Models\AppNotification\Scopes\Unread as UnreadAppNotification;
use App\Models\InternalNotification\Scopes\Unread as UnreadInternalNotification;
use App\Notifications\UnreadNotificationCountUpdatedNotification;
use Auth;
use Illuminate\Support\Carbon;
use Response;
use Throwable;

class MarkAsReadController extends Controller
{
    /**
     * @throws Throwable
     */
    public function __invoke()
    {
        $user = Auth::user();
        $user->internalNotifications()->scoped(new UnreadInternalNotification())->update([
            'read_at' => Carbon::now(),
        ]);
        $user->appNotifications()->scoped(new UnreadAppNotification())->update([
            'read' => true,
        ]);
        $user->notify(new UnreadNotificationCountUpdatedNotification(0));

        return Response::noContent();
    }
}

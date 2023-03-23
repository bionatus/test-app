<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V2\InternalNotification\BaseResource;
use App\Models\InternalNotification;
use App\Models\Scopes\Latest;
use Auth;

class InternalNotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $internalNotifications = $user->internalNotifications()->scoped(new Latest())->paginate();

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

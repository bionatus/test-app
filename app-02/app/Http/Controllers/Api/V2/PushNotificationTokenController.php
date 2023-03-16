<?php

namespace App\Http\Controllers\Api\V2;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\PushNotificationToken\StoreRequest;
use App\Http\Resources\Api\V2\PushNotificationToken\BaseResource;
use App\Models\Device;
use App\Models\PushNotificationToken;
use Auth;
use Symfony\Component\HttpFoundation\Response;

class PushNotificationTokenController extends Controller
{
    public function store(StoreRequest $request)
    {
        $device = Device::updateOrCreate([
            'udid' => $request->get(RequestKeys::DEVICE),
        ], [
            'udid'        => $request->get(RequestKeys::DEVICE),
            'app_version' => $request->get(RequestKeys::VERSION),
            'user_id'     => Auth::id(),
            'token'       => $request->bearerToken(),
        ]);

        $pushNotificationToken = PushNotificationToken::updateOrCreate([
            'device_id' => $device->getKey(),
        ], [
            'token'     => $request->get(RequestKeys::TOKEN),
            'os'        => $request->get(RequestKeys::OS),
            'device_id' => $device->getKey(),
        ]);

        return (new BaseResource($pushNotificationToken->fresh()))->response()->setStatusCode(Response::HTTP_CREATED);
    }
}

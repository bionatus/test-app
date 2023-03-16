<?php

namespace App\Http\Controllers\Api\V3;

use App;
use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Account\ShowRequest;
use App\Http\Resources\Api\V3\Account\DetailedResource;
use App\Models\AppVersion;
use App\Models\Scopes\Latest;
use Auth;
use Response;
use Throwable;

class AccountController extends Controller
{
    public function show(ShowRequest $request)
    {
        $user  = Auth::user();
        $token = $request->bearerToken();
        /** @var AppVersion $appVersion */
        $appVersion    = AppVersion::scoped(new Latest())->first();
        $clientVersion = $request->get(RequestKeys::VERSION, '');

        return new DetailedResource($user, $token, $appVersion, $clientVersion);
    }

    /**
     * @throws Throwable
     */
    public function delete()
    {
        $user = Auth::user();
        $user->delete();

        return Response::noContent();
    }
}

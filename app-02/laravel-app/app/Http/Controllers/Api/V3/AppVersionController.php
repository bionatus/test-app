<?php

namespace App\Http\Controllers\Api\V3;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\AppVersion\InvokeRequest;
use App\Http\Resources\Api\V3\AppVersion\BaseResource;
use App\Models\AppVersion;
use App\Models\Scopes\Latest;

class AppVersionController extends Controller
{
    public function __invoke(InvokeRequest $request)
    {
        /** @var AppVersion $appVersion */
        $appVersion    = AppVersion::scoped(new Latest())->first();
        $clientVersion = $request->get(RequestKeys::VERSION);

        return new BaseResource($appVersion, $clientVersion);
    }
}

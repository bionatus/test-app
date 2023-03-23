<?php

namespace App\Http\Controllers\Api\V3\Account;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V3\Account\Point\BaseResource;
use App\Models\AppSetting;
use App\Models\Scopes\ByRouteKey;
use Auth;

class PointController extends Controller
{
    public function __invoke()
    {
        /** @var AppSetting $appSettingMultiplier */
        $appSettingMultiplier = AppSetting::scoped(new ByRouteKey(AppSetting::SLUG_BLUON_POINTS_MULTIPLIER))->first();
        $user                 = Auth::user();

        return new BaseResource($user, $appSettingMultiplier);
    }
}

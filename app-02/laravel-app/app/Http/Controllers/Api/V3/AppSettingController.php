<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V3\AppSetting\BaseResource;
use App\Models\AppSetting;

class AppSettingController extends Controller
{
    public function show(AppSetting $appSetting)
    {
        return new BaseResource($appSetting);
    }
}

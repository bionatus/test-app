<?php

namespace App\Http\Controllers\LiveApi\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\LiveApi\V1\AppSetting\BaseResource;
use App\Models\AppSetting;

class AppSettingController extends Controller
{
    public function show(AppSetting $appSetting)
    {
        return new BaseResource($appSetting);
    }
}

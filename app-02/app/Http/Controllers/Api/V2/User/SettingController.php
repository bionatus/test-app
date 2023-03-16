<?php

namespace App\Http\Controllers\Api\V2\User;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\User\Setting\UpdateRequest;
use App\Http\Resources\Api\V2\User\Setting\BaseResource;
use App\Models\Setting;
use Auth;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class SettingController extends Controller
{
    public function update(UpdateRequest $request, Setting $setting)
    {
        $user = Auth::user();

        $settingUser = $user->setSetting($setting, $request->get(RequestKeys::VALUE));

        return (new BaseResource($settingUser))->response()->setStatusCode(HttpResponse::HTTP_OK);
    }
}

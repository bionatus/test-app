<?php

namespace App\Http\Controllers\Api\V3\AppVersion;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\AppVersion\Confirm\InvokeRequest;
use App\Models\AppVersion;
use App\Models\Flag;
use App\Models\Scopes\Latest;
use Auth;
use DB;
use Lang;
use Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

class ConfirmController extends Controller
{
    /**
     * @throws Throwable
     */
    public function __invoke(InvokeRequest $request)
    {
        /** @var AppVersion $appVersion */
        $appVersion = AppVersion::scoped(new Latest())->first();
        $flag       = Lang::get(Flag::APP_VERSION_CONFIRM, ['app_version' => $appVersion->current]);
        $seconds    = $request->get(RequestKeys::SECONDS);
        $user       = Auth::user();

        DB::transaction(function() use ($user, $flag, $appVersion, $seconds) {
            $user->flag($flag);
            $user->videoElapsedTimes()->updateOrCreate([
                    'version' => $appVersion->current
                ], [
                    'version' => $appVersion->current,
                    'seconds' => $seconds,
                ]);
        });

        return Response::noContent(HttpResponse::HTTP_CREATED);
    }
}

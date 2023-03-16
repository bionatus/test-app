<?php

namespace App\Http\Middleware;

use App\Constants\RouteParameters;
use App\Models\Phone;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ValidateIfPhoneCanMakeSMSRequests
{
    public function handle(Request $request, Closure $next)
    {
        /** @var Phone $assignedVerifiedPhone */
        $assignedVerifiedPhone  = $request->route()->parameter(RouteParameters::ASSIGNED_VERIFIED_PHONE);
        $nextRequestAvailableAt = $assignedVerifiedPhone->nextRequestAvailableAt();
        $phoneCanMakeSMSRequest = Carbon::now()->gte($nextRequestAvailableAt);

        if (!$phoneCanMakeSMSRequest) {
            abort(403, 'The phone will be available at ' . $nextRequestAvailableAt);
        }

        return $next($request);
    }
}

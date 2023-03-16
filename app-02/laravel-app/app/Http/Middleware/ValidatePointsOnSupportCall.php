<?php

namespace App\Http\Middleware;

use App\Models\SupportCall;
use Auth;
use Closure;
use Illuminate\Http\Request;

class ValidatePointsOnSupportCall
{
    const SUPPORT_CALL_DISABLED = 'Support call disabled.';

    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user->isSupportCallDisabled() && ($user->totalPointsEarned() < SupportCall::MINIMUM_POINTS_TO_CALL)) {
            abort(403, self::SUPPORT_CALL_DISABLED);
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use App\Models\ApiUsage;
use Auth;
use Closure;
use Config;
use DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class LogUserApiUsage extends BaseMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $enabled = Config::get('api-usage.log_requests');
        if (!$enabled) {
            return $next($request);
        }

        if (!$user = Auth::user()) {
            return $next($request);
        }

        try {
            DB::transaction(function() use ($user) {
                $timezone = Config::get('api-usage.tracking_timezone');
                $userId   = $user->getKey();
                $date     = Carbon::now($timezone)->startOfDay();
                ApiUsage::sharedLock()->firstOrCreate([
                    'user_id' => $userId,
                    'date'    => $date,
                ]);
            });
        } catch (QueryException $exception) {
            //silently ignore
        }

        return $next($request);
    }
}

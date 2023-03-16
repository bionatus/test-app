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

class LogSupplierApiUsage extends BaseMiddleware
{
    /**
     * @throws \Throwable
     */
    public function handle(Request $request, Closure $next)
    {
        $enabled = Config::get('api-usage.log_requests');
        if (!$enabled) {
            return $next($request);
        }

        if (!$staff = Auth::user()) {
            return $next($request);
        }

        try {
            DB::transaction(function() use ($staff) {
                $timezone   = Config::get('api-usage.tracking_timezone');
                $supplierId = $staff->supplier->getKey();
                $date       = Carbon::now($timezone)->startOfDay();
                ApiUsage::sharedLock()->firstOrCreate([
                    'supplier_id' => $supplierId,
                    'date'        => $date,
                ]);
            });
        } catch (QueryException $exception) {
            // Silently ignored
        }

        return $next($request);
    }
}

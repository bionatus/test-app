<?php

namespace App\Http\Middleware;

use Closure;

class ApiAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $authKey = 'Bearer ' . config('app.api_key');

        if ($request->header('authorization') !== $authKey) {
            abort(401, 'Requested route couldn\'t be found.');
        }

        return $next($request);
    }
}

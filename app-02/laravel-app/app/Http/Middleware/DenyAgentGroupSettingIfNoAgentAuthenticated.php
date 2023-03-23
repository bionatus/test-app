<?php

namespace App\Http\Middleware;

use App\Constants\RouteParameters;
use App\Models\Setting;
use App\Models\User;
use Auth;
use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class DenyAgentGroupSettingIfNoAgentAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        /** @var Setting $setting */
        $setting = $request->route()->parameter(RouteParameters::SETTING_USER);

        if (!$setting->isGroupAgent()) {
            return $next($request);
        }

        if (!Auth::check()) {
            throw (new ModelNotFoundException)->setModel(Setting::class);
        }

        $user = Auth::user() instanceof User ? Auth::user() : User::find(Auth::id());

        if (!$user->isAgent()) {
            throw (new ModelNotFoundException)->setModel(Setting::class);
        }

        return $next($request);
    }
}

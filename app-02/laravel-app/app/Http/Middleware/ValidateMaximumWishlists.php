<?php

namespace App\Http\Middleware;

use App;
use Auth;
use Closure;
use Illuminate\Http\Request;
use Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ValidateMaximumWishlists
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if ($user->wishlists()->count() >= 10) {
            return Response::noContent(HttpResponse::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}

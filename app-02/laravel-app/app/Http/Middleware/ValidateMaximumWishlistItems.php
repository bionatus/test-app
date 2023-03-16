<?php

namespace App\Http\Middleware;

use App;
use App\Constants\RouteParameters;
use Closure;
use Illuminate\Http\Request;
use Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ValidateMaximumWishlistItems
{
    public function handle(Request $request, Closure $next)
    {
        $wishlist = $request->route()->parameter(RouteParameters::WISHLIST);
        if ($wishlist->itemWishlists()->count() >= 50) {
            return Response::noContent(HttpResponse::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}

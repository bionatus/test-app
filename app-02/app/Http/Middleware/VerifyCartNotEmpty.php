<?php

namespace App\Http\Middleware;

use App;
use Auth;
use Closure;
use Illuminate\Http\Request;
use Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class VerifyCartNotEmpty
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $cart = $user->cart;

        if (!$cart || $cart->cartItems()->doesntExist()) {
            return Response::noContent(HttpResponse::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}

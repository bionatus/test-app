<?php

namespace App\Http\Middleware;

use App;
use App\Constants\RouteParameters;
use App\Models\SupplierInvitation;
use App\Models\SupplierInvitation\Scopes\BySupplier;
use App\Models\Scopes\ByUser;
use App\Models\Supplier;
use Auth;
use Closure;
use Illuminate\Http\Request;
use Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ValidateSupplierInvitation
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        /** @var Supplier $supplier */
        $supplier = $request->route()->parameter(RouteParameters::SUPPLIER);

        $query = SupplierInvitation::query();
        $query->scoped(new ByUser($user));
        $query->scoped(new BySupplier($supplier));

        if ($query->exists()) {
            return Response::noContent(HttpResponse::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}

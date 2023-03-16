<?php

namespace App\Http\Controllers\Api\V4\Supplier;

use App;
use App\Actions\Models\Cart\GetCart;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V4\Supplier\DefaultSupplier\BaseResource;
use Auth;
use Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class DefaultSupplierController extends Controller
{
    public function __invoke()
    {
        $user = Auth::user();

        $cart = App::make(GetCart::class, ['user' => $user])->execute();

        $supplier = $cart->supplier;

        if (!$supplier) {
            return Response::json(['data' => null], HttpResponse::HTTP_OK);
        }

        return new BaseResource($supplier);
    }
}

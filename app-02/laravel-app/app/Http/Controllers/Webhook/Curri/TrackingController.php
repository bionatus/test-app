<?php

namespace App\Http\Controllers\Webhook\Curri;

use App;
use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Models\CurriDelivery;
use App\Models\CurriDelivery\Scopes\ByBookId;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class TrackingController extends Controller
{
    public function __invoke(Request $request)
    {
        /** @var CurriDelivery $curriDelivery */
        $curriDelivery = CurriDelivery::scoped(new ByBookId($request->get(RequestKeys::ID)))->first();

        if ($curriDelivery) {
            $curriDelivery->status = $request->get(RequestKeys::STATUS);
            $curriDelivery->save();
        }

        return response()->noContent(HttpResponse::HTTP_OK);
    }
}

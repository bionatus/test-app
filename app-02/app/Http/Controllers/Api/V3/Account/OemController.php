<?php

namespace App\Http\Controllers\Api\V3\Account;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Account\Oem\StoreRequest;
use App\Http\Resources\Api\V3\Account\Oem\BaseResource;
use App\Models\Oem;
use App\Models\Oem\Scopes\Live;
use App\Models\Scopes\ByUuid;
use Auth;
use Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class OemController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $query = $user->favoriteOems()
            ->with('series', 'series.brand')
            ->scoped(new Live())
            ->orderByPivot('created_at', 'desc');

        $page = $query->paginate();

        return BaseResource::collection($page);
    }

    public function store(StoreRequest $request)
    {
        $user = Auth::user();

        /** @var Oem $oem */
        $oem = Oem::scoped(new ByUuid($request->get(RequestKeys::OEM)))->first();
        $user->favoriteOems()->syncWithoutDetaching($oem->getKey());

        return (new BaseResource($oem))->response()->setStatusCode(HttpResponse::HTTP_CREATED);
    }

    public function delete(Oem $oem)
    {
        $user = Auth::user();

        $user->favoriteOems()->detach($oem->getKey());

        return Response::noContent();
    }
}

<?php

namespace App\Http\Controllers\Api\V3\Account;

use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Account\BulkFavoriteSeries\InvokeRequest;
use App\Http\Resources\Api\V3\Account\BulkFavoriteSeries\BaseResource;
use Auth;
use Symfony\Component\HttpFoundation\Response;

class BulkFavoriteSeriesController extends Controller
{
    public function __invoke(InvokeRequest $request)
    {
        $user = Auth::user();
        $user->favoriteSeries()->sync($request->get(RequestKeys::SERIES));

        return BaseResource::collection($user->favoriteSeries()->paginate())
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}

<?php

namespace App\Http\Controllers\LiveApi\V1\Address\Country;

use App\Constants\Locales;
use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveApi\V1\Address\Country\State\IndexRequest;
use App\Http\Resources\LiveApi\V1\Address\Country\State\BaseResource;
use Illuminate\Support\Collection;
use MenaraSolutions\Geographer\Country;

class StateController extends Controller
{
    public function index(IndexRequest $request, Country $country)
    {
        $locale     = $request->get(RequestKeys::LOCALE) ?? Locales::EN;
        $statesList = Collection::make($country->getStates()->setLocale($locale)->useShortNames()->sortBy('name'))
            ->values();

        return BaseResource::collection($statesList);
    }
}

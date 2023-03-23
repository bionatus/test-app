<?php

namespace App\Http\Controllers\Api\Nova\Address\Country;

use App\Constants\Locales;
use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Nova\Address\Country\State\IndexRequest;
use App\Http\Resources\Api\Nova\Address\Country\State\StateResource;
use Illuminate\Support\Collection;
use MenaraSolutions\Geographer\Country;

class StateController extends Controller
{
    public function index(IndexRequest $request, Country $country)
    {
        $locale     = $request->get(RequestKeys::LOCALE) ?? Locales::EN;
        $statesList = Collection::make($country->getStates()->setLocale($locale)->useShortNames()->sortBy('name'))
            ->values();

        StateResource::withoutWrapping();

        return StateResource::collection($statesList);
    }
}

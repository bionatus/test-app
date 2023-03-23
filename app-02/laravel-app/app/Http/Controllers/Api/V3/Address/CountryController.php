<?php

namespace App\Http\Controllers\Api\V3\Address;

use App\Constants\Locales;
use App\Constants\RequestKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V3\Address\Country\IndexRequest;
use App\Http\Resources\Api\V3\Address\Country\BaseResource;
use Config;
use Illuminate\Support\Collection;
use MenaraSolutions\Geographer\Earth;

class CountryController extends Controller
{
    public function index(IndexRequest $request)
    {
        $locale        = $request->get(RequestKeys::LOCALE) ?? Locales::EN;
        $geo           = new Earth();
        $countriesList = Collection::make($geo->getCountries()->setLocale($locale)->useShortNames()->sortBy('name'))
            ->filter(fn($country) => in_array($country->code, Config::get('communications.allowed_countries')))
            ->values();

        return BaseResource::collection($countriesList);
    }
}

<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Laravel\Spark\Repositories\Geography\CountryRepository;

class CountriesController extends Controller
{
    /**
     * Get list of all countries.
     *
     * @param  Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $countries = new CountryRepository();
        
        return array_values(collect($countries->all())->map(function($country, $key){
            return [
                'value' => $key,
                'label' => $country,
            ];
        })->toArray());
    }
}

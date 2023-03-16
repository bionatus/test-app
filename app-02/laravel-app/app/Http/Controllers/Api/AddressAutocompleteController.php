<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use SKAgarwal\GoogleApi\PlacesApi;

class AddressAutocompleteController extends Controller
{
    /**
     * Autocomplete addresses
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $input = $request->get('address');

        $googlePlaces = new PlacesApi(env('GOOGLE_MAPS_API_KEY'));

        $response = $googlePlaces->placeAutocomplete($input, ['types' => 'geocode']);

        if ($response['status'] === 'OK') {
            return $response['predictions']->map(function($place) {
                return [
                    'placeId' => $place['place_id'],
                    'description' => $place['description'],
                    'mainText' => $place['structured_formatting']['main_text'],
                    // 'secondaryText' => $place['structured_formatting']['secondary_text'],
                ];
            });
        }

        return [];
    }
}

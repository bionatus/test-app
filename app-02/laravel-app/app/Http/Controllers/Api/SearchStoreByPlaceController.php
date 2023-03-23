<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Store;
use SKAgarwal\GoogleApi\PlacesApi;

class SearchStoreByPlaceController extends Controller
{
    /**
     * Get all stores
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $placeId = $request->get('placeId');

        $googlePlaces = new PlacesApi(env('GOOGLE_MAPS_API_KEY'));

        $response = $googlePlaces->placeDetails($placeId);

        if ($response['status'] === 'OK' && $response['result']) {
            $stores = Store::geofence($response['result']['geometry']['location']['lat'], $response['result']['geometry']['location']['lng'], 0, 50)->get();

            return $stores->map(function($store) {
                return [
                    'id' => $store->id,
                    'name' => $store->name,
                    'address' => $store->address,
                    'city' => $store->city,
                    'state' => $store->state,
                    'zip' => $store->zip,
                    'phone' => $store->phone,
                    'country' => $store->country_iso,
                    'image' => asset('storage/' . $store->image),
                    'distance' => $store->distance,
                    'lat' => $store->lat,
                    'lng' => $store->lng,
                ];
            })->sortBy('distance')->toJson();
        }

        return [];
    }
}

<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Store;

class SearchStoreController extends Controller
{
    /**
     * Get all stores
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $lat = $request->get('lat');
        $lng = $request->get('lng');

        $stores = Store::geofence($lat, $lng, 0, 50)->get();

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
}

<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Store;

class StoreController extends Controller
{
    /**
     * Get all stores
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $stores = Store::all();

        return $stores->map(function($store) {
            return [
                'name' => $store->name,
                'address' => $store->address,
                'city' => $store->city,
                'state' => $store->state,
                'zip' => $store->zip,
                'phone' => $store->phone,
                'country' => $store->country_iso,
                'image' => asset('storage/' . $store->image),
                'lat' => $store->lat,
                'lng' => $store->lng,
            ];
        })->toJson();
    }
}

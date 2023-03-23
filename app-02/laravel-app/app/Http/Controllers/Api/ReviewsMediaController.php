<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ReviewsMediaController extends Controller
{
    /**
     * Handle a reviews media request to the application.
     *
     * @param  Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        return response()->json([
            'type' => 'image', // image|video
            'url' => asset(Storage::url('reviewsMain@3x.png')), // imageURL | YouTube video ID
        ]);
    }
}

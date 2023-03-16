<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Review;
use Illuminate\Support\Facades\Storage;

class ReviewsController extends Controller
{
    /**
     * Handle a reviews request to the application.
     *
     * @param  Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $reviews = Review::paginate(5);

        return response()->json([
            'nextPageUrl' => $reviews->nextPageUrl(),
            'prevPageUrl' => $reviews->previousPageUrl(),
            'hasNextPage' => $reviews->hasMorePages(),
            'nextPage' => $reviews->hasMorePages() ? $reviews->currentPage() + 1 : null,
            'reviews' => $reviews->map(function($review) {
                preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $review->video_url, $videoId);

                return [
                    'id' => $review->id,
                    'title' => $review->title,
                    'summary' => $review->summary,
                    'score' => $review->score,
                    'price' => $review->price,
                    'salePrice' => $review->sale_price,
                    'image' => !empty($review->image) ? asset(Storage::url($review->image)) : '',
                    'video' => $videoId[1],
                    'urlLabel' => $review->url_label,
                    'url' => $review->url,
                    'review' => $review->review,
                    'value' => $review->value,
                    'utility' => $review->utility,
                    'bottomSummary' => $review->summary,
                    'descriptionTitle' => 'Our Take',
                    'buyTitle' => 'Buy It Now',
                ];
            })->toArray(),
        ]);
    }

    /**
     * Handle a review info request to the application.
     *
     * @param  Illuminate\Http\Request $request
     * @param  String $review
     * @return Illuminate\Http\JsonResponse
     */
    public function info(Request $request, String $review)
    {
        $review = Review::where('id', $review)->firstOrFail();

        return response()->json([
            'id' => $review->id,
            'title' => $review->title,
            'summary' => $review->summary,
            'score' => $review->score,
            'price' => $review->price,
            'salePrice' => $review->sale_price,
            'image' => !empty($review->image) ? asset(Storage::url($review->image)) : '',
            'video' => $review->video_url,
            'urlLabel' => $review->url_label,
            'url' => $review->url,
            'review' => $review->review,
            'value' => $review->value,
            'utility' => $review->utility,
            'bottomSummary' => $review->summary,
            'descriptionTitle' => 'Our Take',
            'buyTitle' => 'Buy It Now',
        ]);
    }
}

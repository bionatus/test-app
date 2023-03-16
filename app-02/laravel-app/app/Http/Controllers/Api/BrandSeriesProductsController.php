<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Product;

class BrandSeriesProductsController extends Controller
{
    /**
     * Get a list of the available series for a brand
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Integer                   $brandId
     * @param  Integer                   $seriesId
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, int $brandId, int $seriesId)
    {
        $searchString = strtolower($request->input('search'));

        if ($searchString) {
            $products = Product::where('model', 'like', "%{$searchString}%")
                ->where('series_id', $seriesId)
                    ->orderBy('model', 'ASC')
                    ->select('model')
                    ->paginate(config('app.items_per_age'));
        } else {
            $products = Product::where('series_id', $seriesId)
                ->orderBy('model', 'ASC')
                ->select('model')
                ->paginate(config('app.items_per_age'));
        }


        return response()->json([
            'hasNextPage' => $products->hasMorePages(),
            'nextPage' => $products->hasMorePages() ? $products->currentPage() + 1 : null,
            'products' => $products->map(function($product) {
                return $product->model;
            }),
        ]);
    }
}

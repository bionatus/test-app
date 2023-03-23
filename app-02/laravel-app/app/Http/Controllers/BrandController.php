<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Brand;
use App\Product;

class BrandController extends Controller
{
    /**
     * Get a list of the available brands - Legacy
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        return Brand::withCount('series')->orderBy('name', 'asc')
            ->paginate(config('app.items_per_age'));
    }

    /**
     * Get a list of the available brands
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $searchString = strtolower($request->input('search'));

        if ($searchString) {
            $brands = Brand::withCount('series')->where('name', 'like', "%{$searchString}%")
                ->orderBy('name', 'ASC')
                ->paginate(config('app.items_per_age'));
        } else {
            $brands = Brand::withCount('series')->orderBy('name', 'ASC')
                ->paginate(config('app.items_per_age'));
        }


        return response()->json([
            'hasNextPage' => $brands->hasMorePages(),
            'nextPage' => $brands->hasMorePages() ? $brands->currentPage() + 1 : null,
            'brands' => $brands->map(function($brand) {
                return $brand;
            }),
        ]);
    }

    /**
     * Get a list of the available brands
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  String                    $brand
     * @return \Illuminate\Http\Response
     */
    public function products(Request $request, string $brand)
    {
        // No check is performed on $brand existance
        // as it would simply return no results
        return Product::where('brand', $brand)
            ->orderBy('model', 'ASC')
            ->select('model')
            ->paginate(config('app.items_per_age'));
    }

    /**
     * Search products by provided search string
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  String                    $brand
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request, string $brand)
    {
        $searchString = strtolower($request->get('s'));

        return Product::select('model')
            ->where('brand', $brand)
            ->where('model', 'like', "%{$searchString}%")
            ->paginate(config('app.items_per_age'));
    }
}

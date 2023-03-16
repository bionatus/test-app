<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Product;
use App\Warning;

class ProductWarningsController extends Controller
{
	/**
     * Provides conversion data for the selected product
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  String                    $product
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, string $product) {
    	$productItem = Product::where('model', $product)->firstOrFail();
        $productWarnings = data_get($productItem, 'fields.Warnings', []);

        $warnings = Warning::whereIn('title', $productWarnings)->get();

        return response()->json($warnings);
    }
}

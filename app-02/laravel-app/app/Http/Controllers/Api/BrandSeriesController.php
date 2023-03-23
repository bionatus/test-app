<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class BrandSeriesController extends Controller
{
    public function index(Request $request, int $brandId)
    {
        $searchString = strtolower($request->input('search'));

        /** @var Collection $series */
        if ($searchString) {
            $series = Series::where('name', 'like', "%{$searchString}%")
                ->whereHas('brand', function($query) use ($brandId) {
                    return $query->where('id', $brandId);
                })
                ->orderBy('name', 'ASC')
                ->paginate();
        } else {
            $series = Series::whereHas('brand', function($query) use ($brandId) {
                return $query->where('id', $brandId);
            })->orderBy('name', 'ASC')->paginate();
        }

        return response()->json([
            'hasNextPage' => $series->hasMorePages(),
            'nextPage'    => $series->hasMorePages() ? $series->currentPage() + 1 : null,
            'series'      => $series->map(function($seriesItem) {
                return [
                    'id'       => $seriesItem->id,
                    'image'    => $seriesItem->image,
                    'name'     => $seriesItem->name,
                    'brand_id' => $seriesItem->brand->id,
                    'system'   => [],
                ];
            }),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\ConversionJob;
use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Provides the product data
     *
     * @param \Illuminate\Http\Request $request
     * @param String                   $product
     *
     * @return \Illuminate\Http\Response
     */
    public function info(Request $request, string $product)
    {
        $product = Product::where('model', $product)->firstOrFail();

        return $product->parsed;
    }

    /**
     * Provides conversion data for the selected product
     *
     * @param \Illuminate\Http\Request $request
     * @param String                   $product
     *
     * @return \Illuminate\Http\Response
     */
    public function conversion(Request $request, string $product)
    {
        $productItem = Product::where('model', $product)->firstOrFail();

        $conversionJobs = [
            'System Concerns',
            'Standard Controls',
            'Optional Controls',
        ];

        $getConversionJobs = function ($controls) {
            $jobs = ConversionJob::select(['control', 'standard', 'optional', 'image', 'retrofit'])
                ->whereIn('control', $controls)
                ->get();

            return $jobs->map(function ($job) {
                return [
                    'control'  => $job->control,
                    'standard' => $job->standard,
                    'optional' => $job->optional,
                    'retrofit' => $job->retrofit,
                    'image'    => !empty($job->image) ? asset(Storage::url($job->image)) : '',
                ];
            })->toArray();
        };

        $values = [];

        array_map(function ($controlsKey) use ($productItem, $getConversionJobs, &$values) {
            $controls = !empty($productItem->fields[$controlsKey]) ? $productItem->fields[$controlsKey] : [];

            if (!empty($controls)) {
                $values[$controlsKey] = $getConversionJobs($controls);
            }
        }, $conversionJobs);

        return $values;
    }

    /**
     * Provides manuals data for the selected product
     *
     * @param \Illuminate\Http\Request $request
     * @param String                   $product
     *
     * @return \Illuminate\Http\Response
     */
    public function manuals(Request $request, string $product)
    {
        $productItem = Product::where('model', $product)->firstOrFail();

        $manuals = [
            'Service Facts',
            'Product Data',
            'IOM',
            'Misc',
            'Bluon Guidelines',
            'Diagnostic',
            'Wiring Diagram',
        ];

        $values = [];

        array_map(function ($manualsKey, $manualsText) use ($productItem, &$values) {
            $manual = !empty($productItem->fields[$manualsKey]) ? $productItem->fields[$manualsKey] : [];

            if (!empty($manual)) {
                $values[$manualsText] = $manual;
            }
        }, $manuals, [
            'Service Facts',
            'Product Data',
            'IOM',
            'Miscellaneous',
            'Bluon Guidelines',
            'Troubleshooting/Flash Codes',
            'Wiring Diagram',
        ]);

        return $values;
    }
}

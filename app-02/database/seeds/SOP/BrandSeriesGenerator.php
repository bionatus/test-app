<?php

namespace Database\Seeders\SOP;

use App\Models\Brand;
use App\Models\Series;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BrandSeriesGenerator
{
    private Collection $brands;
    private Collection $series;
    const BRANDS_SERIES = [
        'York UPG' => ['BP', 'D*CG', 'B*HZ'],
        'Trane'    => ['TWX0', 'Voyager', 'TWP0'],
        'Rheem'    => ['RLMB', 'RJMA', 'RJNA', 'RRKA-A', 'RKNB'],
        'Carrier'  => ['38BRG', '48TC', '48SD', '48HJE'],
        'Miller'   => ['R4GE'],
    ];

    public function __construct()
    {
        $this->brands = new Collection();
        $this->series = new Collection();
    }

    public function getBrands(): Collection
    {
        return $this->brands;
    }

    public function getSeries(): Collection
    {
        return $this->series;
    }

    public function createBrandsAndSeries()
    {
        $now = Carbon::now();
        foreach (self::BRANDS_SERIES as $brandName => $seriesNames) {
            $brand = Brand::firstWhere('name', $brandName);
            if (!$brand) {
                $brand = Brand::factory()->create(['name' => $brandName, 'published_at' => $now->toDateTimeString()]);
            }

            $this->brands->put($brand->getKey(), $brand);

            foreach ($seriesNames as $seriesName) {
                $series = Series::where('name', $seriesName)->where('brand_id', $brand->getKey())->first();
                if (!$series) {
                    $series = Series::factory()->usingBrand($brand)->create([
                        'name'         => $seriesName,
                        'published_at' => $now->toDateTimeString(),
                    ]);
                }

                $this->series->put($series->getKey(), $series);
            }
        }
    }
}

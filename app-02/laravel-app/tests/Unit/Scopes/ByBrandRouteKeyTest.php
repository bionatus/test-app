<?php

namespace Tests\Unit\Scopes;

use App\Models\Brand;
use App\Models\Series;
use App\Scopes\ByBrandRouteKey;
use DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByBrandRouteKeyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_the_result_by_brand_id_column()
    {
        $brand = Brand::factory()->create();
        Series::factory()->usingBrand($brand)->count(5)->create();
        Series::factory()->count(2)->create();

        $scope = new ByBrandRouteKey($brand->getRouteKey());
        $query = DB::table(Series::tableName());
        $scope->apply($query);

        $this->assertCount(5, $query->get());
    }
}

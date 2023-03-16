<?php

namespace Tests\Unit\Models\Brand\Scopes;

use App\Models\Brand;
use App\Models\Brand\Scopes\MostSearched;
use App\Models\BrandDetailCounter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class MostSearchedTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_a_list_of_brand_most_searched_order_by_most_searched()
    {
        $brandsExpected = Collection::make();

        $brand = Brand::factory()->create();
        $brandsExpected->push($brand);
        BrandDetailCounter::factory()->usingBrand($brand)->count(2)->create();

        $brand2 = Brand::factory()->create();
        $brandsExpected->push($brand2);
        BrandDetailCounter::factory()->usingBrand($brand2)->count(3)->create();

        $brand3 = Brand::factory()->create();
        $brandsExpected->push($brand3);
        BrandDetailCounter::factory()->usingBrand($brand3)->count(4)->create();
        $brandsExpected = $brandsExpected->reverse();

        $brands = Brand::scoped(new MostSearched())->get();

        $brands->each(function(Brand $rawBrandSearch) use ($brandsExpected) {
            $brand = $brandsExpected->shift();
            $this->assertSame($brand->getKey(), $rawBrandSearch['id']);
        });
    }
}

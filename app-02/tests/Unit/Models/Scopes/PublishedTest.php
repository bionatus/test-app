<?php

namespace Tests\Unit\Models\Scopes;

use App\Models\Brand;
use App\Models\Scopes\Published;
use App\Models\Series;
use App\Models\Supplier;
use App\Models\XoxoVoucher;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublishedTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_displays_only_published_on_brands()
    {
        Brand::factory()->count(3)->create();
        $brandsPublished = Brand::factory()->count(3)->create(['published_at' => Carbon::now()]);

        $brandsPublishedDb = Brand::scoped(new Published())->get();

        $this->assertEquals($brandsPublished->count(), $brandsPublishedDb->count());

        $brandsPublishedDb->each(function(Brand $brand) use ($brandsPublished) {
            $this->assertSame($brandsPublished->shift()->getKey(), $brand->getKey());
        });
    }

    /** @test */
    public function it_displays_only_published_on_series()
    {
        Series::factory()->count(3)->create();
        $publishedSeries = Series::factory()->count(3)->create(['published_at' => Carbon::now()]);

        $foundSeries = Series::scoped(new Published())->get();

        $this->assertEquals($publishedSeries->count(), $foundSeries->count());

        $foundSeries->each(function(Series $foundSeriesItem) use ($publishedSeries) {
            $this->assertSame($publishedSeries->shift()->getKey(), $foundSeriesItem->getKey());
        });
    }

    /** @test */
    public function it_displays_only_published_on_suppliers()
    {
        Supplier::factory()->count(2)->unpublished()->createQuietly();
        $publishedSuppliers = Supplier::factory()->published()->count(3)->createQuietly();

        $foundSuppliers = Supplier::scoped(new Published())->get();

        $this->assertEquals($publishedSuppliers->count(), $foundSuppliers->count());

        $foundSuppliers->each(function(Supplier $foundSupplierItem) use ($publishedSuppliers) {
            $this->assertSame($publishedSuppliers->shift()->getKey(), $foundSupplierItem->getKey());
        });
    }

    /** @test */
    public function it_displays_only_published_on_xoxo_vouchers()
    {
        XoxoVoucher::factory()->count(2)->unpublished()->create();
        $publishedXoxoVouchers = XoxoVoucher::factory()->published()->count(3)->create();

        $foundXoxoVouchers = XoxoVoucher::scoped(new Published())->get();

        $this->assertEquals($publishedXoxoVouchers->count(), $foundXoxoVouchers->count());

        $foundXoxoVouchers->each(function(XoxoVoucher $foundXoxoVouchersItem) use ($publishedXoxoVouchers) {
            $this->assertSame($publishedXoxoVouchers->shift()->getKey(), $foundXoxoVouchersItem->getKey());
        });
    }
}

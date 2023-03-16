<?php

namespace Tests\Unit\Models\Scopes;

use App\Models\Brand;
use App\Models\Company;
use App\Models\Oem;
use App\Models\Scopes\BySearchString;
use App\Models\Series;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BySearchStringTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_oem_by_a_non_empty_string()
    {
        Oem::factory()->count(2)->create(['model' => 'BTC090CD00F0']);
        Oem::factory()->create(['model' => '30XV49SEARCH600301']);
        Oem::factory()->create(['model' => 'SEARCH3MPSA03CGCM13E']);
        Oem::factory()->create(['model' => 'GPC1360H41SEARCH']);

        $oems = Oem::scoped(new BySearchString('SEARCH', 'model'))->get();

        $this->assertCount(3, $oems);
    }

    /** @test */
    public function it_filters_oem_by_an_empty_string()
    {
        Oem::factory()->count(3)->create();

        $oems = Oem::scoped(new BySearchString('', 'model'))->get();

        $this->assertCount(3, $oems);
    }

    /** @test */
    public function it_filters_oem_by_null()
    {
        Oem::factory()->count(3)->create();

        $oems = Oem::scoped(new BySearchString(null, 'model'))->get();

        $this->assertCount(3, $oems);
    }

    /** @test */
    public function it_filters_brand_by_a_non_empty_string()
    {
        Brand::factory()->create(['name' => 'Name Lorem']);
        Brand::factory()->create(['name' => 'Name Lorem II']);
        Brand::factory()->create(['name' => 'Dr. Donnell Toy I']);
        Brand::factory()->create(['name' => 'Dr. Emory Sporer']);
        Brand::factory()->create(['name' => 'GPC13Dr41SEARCH']);

        $brands = Brand::scoped(new BySearchString('Dr'))->get();

        $this->assertCount(3, $brands);
    }

    /** @test */
    public function it_filters_brand_by_an_empty_string()
    {
        Brand::factory()->count(3)->create();

        $brands = Brand::scoped(new BySearchString(''))->get();

        $this->assertCount(3, $brands);
    }

    /** @test */
    public function it_filters_brand_by_null()
    {
        Brand::factory()->count(3)->create();

        $brands = Brand::scoped(new BySearchString(null))->get();

        $this->assertCount(3, $brands);
    }

    /** @test */
    public function it_filters_supplier_by_a_non_empty_string()
    {
        Supplier::factory()->count(2)->createQuietly(['name' => 'A special store']);
        Supplier::factory()->createQuietly(['name' => 'A regular store']);
        Supplier::factory()->createQuietly(['name' => 'Regular store']);
        Supplier::factory()->createQuietly(['name' => 'Store regular']);

        $stores = Supplier::scoped(new BySearchString('regular'))->get();

        $this->assertCount(3, $stores);
    }

    /** @test */
    public function it_filters_supplier_by_an_empty_string()
    {
        Supplier::factory()->count(3)->createQuietly();

        $stores = Supplier::scoped(new BySearchString(''))->get();

        $this->assertCount(3, $stores);
    }

    /** @test */
    public function it_filters_supplier_by_null()
    {
        Supplier::factory()->count(3)->createQuietly();

        $stores = Supplier::scoped(new BySearchString(null))->get();

        $this->assertCount(3, $stores);
    }

    /** @test */
    public function it_filters_series_by_a_non_empty_string()
    {
        Series::factory()->count(2)->create(['name' => 'SERIES0001']);
        Series::factory()->create(['name' => 'SERIES0002SEARCH']);
        Series::factory()->create(['name' => 'SEARCHSERIES0003']);
        Series::factory()->create(['name' => 'SERIES0004']);

        $series = Series::scoped(new BySearchString('SEARCH'))->get();

        $this->assertCount(2, $series);
    }

    /** @test */
    public function it_filters_series_by_an_empty_string()
    {
        Series::factory()->count(3)->create();

        $series = Series::scoped(new BySearchString(''))->get();

        $this->assertCount(3, $series);
    }

    /** @test */
    public function it_filters_series_by_null()
    {
        Series::factory()->count(3)->create();

        $series = Series::scoped(new BySearchString(null))->get();

        $this->assertCount(3, $series);
    }

    /** @test */
    public function it_filters_companies_by_a_non_empty_string()
    {
        Company::factory()->count(2)->create(['name' => 'SERIES0001']);
        Company::factory()->create(['name' => 'SERIES0002SEARCH']);
        Company::factory()->create(['name' => 'SEARCHSERIES0003']);
        Company::factory()->create(['name' => 'SERIES0004']);

        $companies = Company::scoped(new BySearchString('SEARCH'))->get();

        $this->assertCount(2, $companies);
    }
}

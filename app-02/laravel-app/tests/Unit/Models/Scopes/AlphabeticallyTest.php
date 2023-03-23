<?php

namespace Tests\Unit\Models\Scopes;

use App\Models\Brand;
use App\Models\Company;
use App\Models\Oem;
use App\Models\Scopes\Alphabetically;
use App\Models\Series;
use App\Models\Supplier;
use App\Models\XoxoVoucher;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Tests\TestCase;

class AlphabeticallyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_orders_by_oem_model_alphabetically()
    {
        $scopeField = 'model';
        $oems       = Oem::factory()->count(20)->create()->sortBy($scopeField);

        $orderedOems = Oem::scoped(new Alphabetically($scopeField))->get();

        $orderedOems->each(function(Oem $oem) use ($oems) {
            $this->assertSame($oems->shift()->getKey(), $oem->getKey());
        });
    }

    /** @test */
    public function it_orders_by_brand_name_alphabetically()
    {
        $brands = Brand::factory()->count(20)->create()->sortBy('name');

        $orderedBrands = Brand::scoped(new Alphabetically())->get();

        $orderedBrands->each(function(Brand $brand) use ($brands) {
            $this->assertSame($brands->shift()->getKey(), $brand->getKey());
        });
    }

    /** @test */
    public function it_orders_by_supplier_name_alphabetically()
    {
        $suppliers = Supplier::factory()->count(20)->sequence(fn(Sequence $sequence) => [
            'name' => Str::lower(Str::random(10)),
        ])->createQuietly()->sortBy(function(Supplier $supplier) {
            return Str::lower($supplier->name);
        });

        $orderedStores = Supplier::scoped(new Alphabetically())->get();

        $orderedStores->each(function(Supplier $supplier) use ($suppliers) {
            $this->assertSame($suppliers->shift()->getKey(), $supplier->getKey());
        });
    }

    /** @test */
    public function it_orders_by_series_name_alphabetically()
    {
        $series = Series::factory()->count(20)->create()->sortBy('name');

        $orderedSeries = Series::scoped(new Alphabetically())->get();

        $orderedSeries->each(function(Series $orderedSeriesItem) use ($series) {
            $this->assertSame($series->shift()->getKey(), $orderedSeriesItem->getKey());
        });
    }

    /** @test */
    public function it_orders_by_xoxo_vouchers_id_asc()
    {
        $xoxoVouchers = XoxoVoucher::factory()->count(20)->create()->sortBy('id');

        $orderedXoxoVouchers = XoxoVoucher::scoped(new Alphabetically('id'))->get();

        $orderedXoxoVouchers->each(function(XoxoVoucher $orderedXoxoVouchersItem) use ($xoxoVouchers) {
            $this->assertSame($xoxoVouchers->shift()->getKey(), $orderedXoxoVouchersItem->getKey());
        });
    }

    /** @test */
    public function it_orders_by_companies_name_alphabetically()
    {
        $companies = Collection::make([]);
        $companies->push(Company::factory()->create(['name' => 'aname']));
        $companies->push(Company::factory()->create(['name' => 'cname']));
        $companies->push(Company::factory()->create(['name' => 'zname']));

        $orderedCompanies = Company::scoped(new Alphabetically())->get();

        $orderedCompanies->each(function(Company $orderedCompaniesItem) use ($companies) {
            $this->assertSame($companies->shift()->getKey(), $orderedCompaniesItem->getKey());
        });
    }
}

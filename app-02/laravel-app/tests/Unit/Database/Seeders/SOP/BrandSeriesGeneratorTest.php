<?php

namespace Tests\Unit\Database\Seeders\SOP;

use Database\Seeders\SOP\BrandSeriesGenerator;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class BrandSeriesGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private array $brandsSeries;

    protected function setUp(): void
    {
        parent::setUp();

        $this->brandsSeries = [
            'York UPG' => ['BP', 'D*CG', 'B*HZ'],
            'Trane'    => ['TWX0', 'Voyager', 'TWP0'],
            'Rheem'    => ['RLMB', 'RJMA', 'RJNA', 'RRKA-A', 'RKNB'],
            'Carrier'  => ['38BRG', '48TC', '48SD', '48HJE'],
            'Miller'   => ['R4GE'],
        ];
    }

    /** @test
     * @throws Exception
     */
    public function it_generates_brands_and_series()
    {
        $brandSeriesHelper = new BrandSeriesGenerator();
        $brandSeriesHelper->createBrandsAndSeries();

        $this->assertDatabaseCount('brands', Collection::make($this->brandsSeries)->count());
        $this->assertDatabaseCount('series', Collection::make($this->brandsSeries)->flatten()->count());
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_a_list_of_created_brands()
    {
        $brandSeriesHelper = new BrandSeriesGenerator();
        $brandSeriesHelper->createBrandsAndSeries();

        $brandNames = array_keys($this->brandsSeries);
        $this->assertEquals($brandNames, $brandSeriesHelper->getBrands()->pluck('name')->toArray());
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_a_list_of_created_series()
    {
        $brandSeriesHelper = new BrandSeriesGenerator();
        $brandSeriesHelper->createBrandsAndSeries();

        $seriesNames = Collection::make($this->brandsSeries)->flatten()->toArray();
        $this->assertEquals($seriesNames, $brandSeriesHelper->getSeries()->pluck('name')->toArray());
    }
}

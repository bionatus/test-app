<?php

namespace Tests\Unit\Scopes;

use App\Models\Brand;
use App\Scopes\Alphabetically;
use DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlphabeticallyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_orders_by_name_alphabetically()
    {
        $brands = Brand::factory()->count(5)->create();

        $orderedNames = $brands->sortBy('name')->pluck('name');

        $scope = new Alphabetically();
        $query = DB::table(Brand::tableName());
        $scope->apply($query);

        $this->assertEquals($orderedNames, $query->get()->pluck('name'));
    }
}

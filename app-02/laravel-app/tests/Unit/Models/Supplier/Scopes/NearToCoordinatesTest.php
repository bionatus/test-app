<?php

namespace Tests\Unit\Models\Supplier\Scopes;

use App\Models\Supplier;
use App\Models\Supplier\Scopes\NearToCoordinates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NearToCoordinatesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_orders_by_distance()
    {
        Supplier::factory()->createManyQuietly([
            ['latitude' => '2', 'longitude' => '2'],
            ['latitude' => '0', 'longitude' => '0'],
            ['latitude' => '1', 'longitude' => '1'],
        ]);

        $suppliers = Supplier::scoped(new NearToCoordinates('0', '0'))->get();

        $this->assertCount(3, $suppliers);
        $suppliers->each(function(Supplier $supplier, int $index) {
            $this->assertEquals($index, $supplier->latitude);
        });
    }

    /** @test */
    public function it_sends_invalid_coordinated_last_with_null_distance()
    {
        Supplier::factory()->createManyQuietly([
            ['latitude' => '2', 'longitude' => '2'],
            ['latitude' => null, 'longitude' => null],
            ['latitude' => '0', 'longitude' => '0'],
            ['latitude' => '1', 'longitude' => '1'],
        ]);

        $suppliers = Supplier::scoped(new NearToCoordinates('0', '0'))->get();
        $this->assertCount(4, $suppliers);
        $this->assertNull($suppliers->last()->distance);
    }
}

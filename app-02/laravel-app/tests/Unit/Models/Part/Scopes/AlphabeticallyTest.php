<?php

namespace Tests\Unit\Models\Part\Scopes;

use App\Models\Part;
use App\Models\Part\Scopes\Alphabetically;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Tests\TestCase;

class AlphabeticallyTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function it_orders_by_part_type_alphabetically_when_subcategory_is_null()
    {
        Part::factory()->functional()->capacitor()->create();
        Part::factory()->functional()->belt()->create();
        Part::factory()->functional()->pressureControl()->create();
        Part::factory()->functional()->motor()->create();
        Part::factory()->functional()->compressor()->create();

        $parts   = Part::all()->sortBy('type');
        $ordered = Part::scoped(new Alphabetically())->get();

        $ordered->each(function(Part $part) use ($parts) {
            $this->assertSame($parts->shift()->getKey(), $part->getKey());
        });
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_orders_by_coalesce_of_subcategory_and_part_type_alphabetically(
        array $part1Data,
        array $part2Data,
        array $part3Data,
        Collection $expectedOrder
    ) {
        $this->refreshDatabase();
        Part::factory()->create($part1Data);
        Part::factory()->create($part2Data);
        Part::factory()->create($part3Data);
        $parts = Part::all();

        $ordered = Part::scoped(new Alphabetically())->get();

        $ordered->each(function(Part $part) use ($expectedOrder, $parts) {
            $this->assertSame($parts->get($expectedOrder->shift())->getKey(), $part->getKey());
        });
    }

    public function dataProvider(): array
    {
        return [
            [
                ['type' => Part::TYPE_AIR_FILTER, 'subcategory' => 'b'],
                ['type' => Part::TYPE_AIR_FILTER, 'subcategory' => 'a'],
                ['type' => Part::TYPE_AIR_FILTER, 'subcategory' => null],
                Collection::make([1, 2, 0]),
            ],
            [
                ['type' => Part::TYPE_AIR_FILTER, 'subcategory' => 'c'],
                ['type' => Part::TYPE_AIR_FILTER, 'subcategory' => 'b'],
                ['type' => Part::TYPE_AIR_FILTER, 'subcategory' => null],
                Collection::make([2, 1, 0]),
            ],
            [
                ['type' => Part::TYPE_AIR_FILTER, 'subcategory' => 'b'],
                ['type' => Part::TYPE_AIR_FILTER, 'subcategory' => 'a'],
                ['type' => Part::TYPE_CAPACITOR, 'subcategory' => null],
                Collection::make([1, 0, 2]),
            ],
            [
                ['type' => Part::TYPE_AIR_FILTER, 'subcategory' => 'c'],
                ['type' => Part::TYPE_CAPACITOR, 'subcategory' => 'b'],
                ['type' => Part::TYPE_BELT, 'subcategory' => 'a'],
                Collection::make([2, 1, 0]),
            ],
        ];
    }
}

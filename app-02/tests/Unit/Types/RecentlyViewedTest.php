<?php

namespace Tests\Unit\Types;

use App\Models\Oem;
use App\Models\Part;
use App\Services\OemPartQuery;
use App\Types\RecentlyViewed;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class RecentlyViewedTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_does_not_allow_invalid_items()
    {
        $this->expectException(Exception::class);

        new RecentlyViewed(['invalid']);
    }

    /**
     * @test
     *
     * @param array $element
     *
     * @dataProvider invalidElementDataProvider
     * @throws Exception
     */
    public function it_requires_object_id_and_object_type_and_viewed_at(array $element)
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid element. The element needs to have a type, id and a date viewed.');

        new RecentlyViewed($element);
    }

    public function invalidElementDataProvider(): array
    {
        return [
            [
                [
                    'object_id'   => 'an-id',
                    'object_type' => 'a-type',
                ],
            ],
            [
                [
                    'object_type' => 'a-type',
                    'viewed_at'   => 'a-date',
                ],
            ],
            [
                [
                    'object_id' => 'an-id',
                    'viewed_at' => 'a-date',
                ],
            ],
        ];
    }

    /** @test */
    public function type_must_be_a_valid_value()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid type.');

        new RecentlyViewed([
            'object_id'   => 001,
            'object_type' => 'an-invalid-type',
            'viewed_at'   => Carbon::now(),
        ]);
    }

    /**
     * @test
     * @dataProvider typeProvider
     */
    public function it_can_be_instantiated($type)
    {
        $item = new RecentlyViewed([
            'object_id'   => 001,
            'object_type' => $type,
            'viewed_at'   => Carbon::now(),
        ]);

        $this->assertInstanceOf(RecentlyViewed::class, $item);
    }

    public function typeProvider(): array
    {
        return [
            [Oem::MORPH_ALIAS],
            [Part::MORPH_ALIAS],
        ];
    }

    /** @test */
    public function it_returns_an_array_representation()
    {
        $this->refreshDatabaseForSingleTest();

        $item = new RecentlyViewed($data = [
            'object_id'   =>  001,
            'object_type' => Part::MORPH_ALIAS,
            'object'      => Part::factory()->make(),
            'viewed_at'   => Carbon::now(),
        ]);

        $arrayRepresentation = $item->toArray();

        $this->assertEquals($data, $arrayRepresentation);
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_its_object_id()
    {
        $item = new RecentlyViewed([
            'object_id'   => $objectId = 001,
            'object_type' => Part::MORPH_ALIAS,
            'viewed_at'   => Carbon::now(),
        ]);

        $expected = $item->objectId();
        $this->assertEquals($expected, $objectId);
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_its_object_type()
    {
        $item = new RecentlyViewed([
            'object_id'   => 001,
            'object_type' => $objectType = Part::MORPH_ALIAS,
            'viewed_at'   => Carbon::now(),
        ]);

        $expected = $item->objectType();
        $this->assertEquals($expected, $objectType);
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_its_object()
    {
        $this->refreshDatabaseForSingleTest();

        $itemWithoutObject = new RecentlyViewed([
            'object_id'   => 001,
            'object_type' => Part::MORPH_ALIAS,
            'viewed_at'   => Carbon::now(),
        ]);

        $expectedNull = $itemWithoutObject->object();
        $this->assertNull($expectedNull);

        $item = new RecentlyViewed([
            'object_id'   => 001,
            'object_type' => Part::MORPH_ALIAS,
            'object'      => $part = Part::factory()->make(),
            'viewed_at'   => Carbon::now(),
        ]);

        $expectedPart = $item->object();
        $this->assertEquals($expectedPart, $part);
    }

    /** @test
     * @throws Exception
     */
    public function it_returns_its_viewed_at()
    {
        $item = new RecentlyViewed([
            'object_id'   => 001,
            'object_type' => Part::MORPH_ALIAS,
            'viewed_at'   => $viewedAt = 'a timestamp',
        ]);

        $expected = $item->viewedAt();
        $this->assertEquals($expected, $viewedAt);
    }

    /** @test */
    public function it_returns_a_oem_part_query()
    {
        $oemPartQuery = RecentlyViewed::query(new Collection());

        $this->assertInstanceOf(OemPartQuery::class, $oemPartQuery);
    }
}

<?php

namespace Tests\Unit\Models;

use App;
use App\Models\IsOrderable;
use App\Models\Media;
use App\Models\Supply;
use ReflectionClass;
use Tests\CanRefreshDatabase;

class SupplyTest extends ModelTestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Supply::tableName(), [
            'id',
            'supply_category_id',
            'name',
            'internal_name',
            'sort',
            'visible_at',
        ]);
    }

    /** @test */
    public function it_implements_is_orderable_interface()
    {
        $reflection = new ReflectionClass(Supply::class);

        $this->assertTrue($reflection->implementsInterface(IsOrderable::class));
    }

    /** @test */
    public function it_returns_the_name_as_a_readable_type()
    {
        $supply       = App::make(Supply::class);
        $supply->name = $name = 'A Supply';

        $this->assertSame($name, $supply->readable_type);
    }

    /** @test */
    public function it_returns_null_if_the_supply_has_not_a_category()
    {
        $supply = App::make(Supply::class);

        $this->assertNull($supply->getCategoryMedia());
    }

    /** @test */
    public function it_returns_supply_category_media()
    {
        $this->refreshDatabaseForSingleTest();

        $supply = Supply::factory()->create();
        Media::factory()->usingSupplyCategory($supply->supplyCategory)->create();

        $this->assertInstanceOf(Media::class, $supply->getCategoryMedia());
    }
}

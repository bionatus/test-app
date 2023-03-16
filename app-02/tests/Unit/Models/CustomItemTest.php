<?php

namespace Tests\Unit\Models;

use App\Models\CustomItem;
use App\Models\IsOrderable;
use ReflectionClass;

class CustomItemTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(CustomItem::tableName(), [
            'id',
            'creator_type',
            'creator_id',
            'name',
        ]);
    }

    /** @test */
    public function it_implements_is_orderable_interface()
    {
        $reflection = new ReflectionClass(CustomItem::class);

        $this->assertTrue($reflection->implementsInterface(IsOrderable::class));
    }

    /** @test */
    public function it_returns_the_name_as_a_readable_type()
    {
        $customItem = CustomItem::factory()->make(['name' => $itemName = 'A Custom Item']);

        $this->assertSame($itemName, $customItem->readable_type);
    }
}

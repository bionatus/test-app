<?php

namespace Tests\Unit\Models\CustomItem;

use App\Models\CustomItem;
use App\Models\Item;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property CustomItem $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = CustomItem::factory()->create();
    }

    /** @test */
    public function it_is_an_item()
    {
        $related = $this->instance->item()->first();

        $this->assertInstanceOf(Item::class, $related);
    }

    /** @test */
    public function it_has_a_creator()
    {
        $customItem = CustomItem::factory()->create();
        $creator     = $customItem->creator()->first();

        $this->assertInstanceOf(User::class, $creator);
    }
}

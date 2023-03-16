<?php

namespace Tests\Unit\Models\Scopes;

use App\Models\Item;
use App\Models\ItemOrder;
use App\Models\Scopes\ByUuid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Str;
use Tests\TestCase;

class ByUuidTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_item_orders_by_uuid()
    {
        $uuid = Str::uuid()->toString();
        $item = ItemOrder::factory()->createQuietly(['uuid' => $uuid]);
        ItemOrder::factory()->count(5)->createQuietly();

        $filtered = ItemOrder::query()->scoped(new ByUuid($uuid))->get();
        $this->assertCount(1, $filtered);
        $this->assertEquals($item->fresh(), $filtered->first());
    }

    /** @test */
    public function it_filters_item_by_uuid()
    {
        $uuid = Str::uuid()->toString();
        $item = Item::factory()->create(['uuid' => $uuid]);
        Item::factory()->count(5)->create();

        $filtered = Item::query()->scoped(new ByUuid($uuid))->get();
        $this->assertCount(1, $filtered);
        $this->assertEquals($item->fresh(), $filtered->first());
    }
}

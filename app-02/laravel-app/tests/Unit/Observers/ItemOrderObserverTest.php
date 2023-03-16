<?php

namespace Tests\Unit\Observers;

use App\Models\ItemOrder;
use App\Observers\ItemOrderObserver;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ItemOrderObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fills_uuid_when_creating()
    {
        $item = ItemOrder::factory()->make(['uuid' => null]);

        $observer = new ItemOrderObserver();

        $observer->creating($item);

        $this->assertNotNull($item->uuid);
    }

    /** @test */
    public function it_does_not_change_uuid_when_not_empty()
    {
        $item = ItemOrder::factory()->make(['uuid' => $uuid = '123456']);

        $observer = new ItemOrderObserver();

        $observer->creating($item);

        $this->assertSame($uuid, $item->uuid);
    }

    /** @test */
    public function it_calls_order_touch_method_when_an_item_order_is_saved()
    {
        $belongsTo = Mockery::mock(BelongsTo::class);
        $belongsTo->shouldReceive('touch')->withNoArgs()->once();

        $itemOrder = Mockery::mock(ItemOrder::class);
        $itemOrder->shouldReceive('order')->withNoArgs()->once()->andReturn($belongsTo);

        $observer = new ItemOrderObserver();

        $observer->saved($itemOrder);
    }
}

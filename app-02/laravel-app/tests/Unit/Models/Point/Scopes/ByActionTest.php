<?php

namespace Tests\Unit\Models\Point\Scopes;

use App\Models\Order;
use App\Models\Point;
use App\Models\Point\Scopes\ByAction;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByActionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_action()
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->approved()->usingSupplier($supplier)->create();
        $point    = Point::factory()->usingOrder($order)->create();

        $anotherOrder = Order::factory()->completed()->usingSupplier($supplier)->create();
        $lastOrder    = Order::factory()->canceled()->usingSupplier($supplier)->create();

        Point::factory()->usingOrder($anotherOrder)->redeemed()->create();
        Point::factory()->usingOrder($lastOrder)->orderCanceled()->create();

        $filtered = Point::scoped(new ByAction(Point::ACTION_ORDER_APPROVED))->get();

        $this->assertCount(1, $filtered);
        $this->assertInstanceOf(Point::class, $filtered->first());
        $this->assertSame($point->getKey(), $filtered->first()->getKey());
    }
}

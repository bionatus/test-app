<?php

namespace Tests\Unit\Models\Order\Scopes;

use App\Models\Order;
use App\Models\Order\Scopes\ByActionWithoutPoints;
use App\Models\Point;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ByActionWithoutPointsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_action_without_points()
    {
        $supplier      = Supplier::factory()->createQuietly();
        $approvedOrder = Order::factory()->approved()->usingSupplier($supplier)->create();
        $anotherOrder  = Order::factory()->completed()->usingSupplier($supplier)->create();
        $canceledOrder = Order::factory()->canceled()->usingSupplier($supplier)->create();

        Point::factory()->usingOrder($anotherOrder)->create();
        Point::factory()->usingOrder($canceledOrder)->orderCanceled()->create();

        $expected = Collection::make([$approvedOrder, $canceledOrder]);

        $filtered = Order::scoped(new ByActionWithoutPoints(Point::ACTION_ORDER_APPROVED))->get();

        $this->assertCount(2, $filtered);
        $filtered->each(function(Order $order) use ($expected) {
            $this->assertSame($expected->shift()->getKey(), $order->getKey());
        });
    }
}

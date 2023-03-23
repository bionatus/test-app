<?php

namespace Tests\Unit\Models\Order\Scopes;

use App\Models\Order;
use App\Models\Order\Scopes\OrderedByStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class OrderedByStatusTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_orders_by_status()
    {
        $orderCompleted = Order::factory()->completed()->createQuietly();
        $orderCanceled  = Order::factory()->canceled()->createQuietly();
        $orderApproved  = Order::factory()->approved()->createQuietly();

        $orders = Collection::make([
            $orderApproved,
            $orderCompleted,
            $orderCanceled,
        ]);

        $ordersRouteKeys = $orders->pluck(Order::routeKeyName())->toArray();

        $scopedOrders          = Order::scoped(new OrderedByStatus())->get();
        $scopedOrdersRouteKeys = $scopedOrders->pluck(Order::routeKeyName())->toArray();

        $this->assertEquals($ordersRouteKeys, $scopedOrdersRouteKeys);
    }
}

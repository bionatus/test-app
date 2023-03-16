<?php

namespace Tests\Unit\Models\Order\Scopes;

use App\Models\Order;
use App\Models\Order\Scopes\OrderedByGroupedStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class OrderedByGroupedStatusTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_orders_by_status()
    {
        $orderCompleted       = Order::factory()->completed()->createQuietly();
        $orderCanceled        = Order::factory()->canceled()->createQuietly();
        $orderPendingApproval = Order::factory()->pendingApproval()->createQuietly();
        $orderPending         = Order::factory()->pending()->createQuietly();
        $orderApproved        = Order::factory()->approved()->createQuietly();

        $orders          = Collection::make([
            $orderPendingApproval,
            $orderPending,
            $orderApproved,
            $orderCompleted,
            $orderCanceled,
        ]);
        $ordersRouteKeys = $orders->pluck(Order::routeKeyName())->toArray();

        $scopedOrders          = Order::scoped(new OrderedByGroupedStatus())->get();
        $scopedOrdersRouteKeys = $scopedOrders->pluck(Order::routeKeyName())->toArray();

        $this->assertEquals($ordersRouteKeys, $scopedOrdersRouteKeys);
    }
}

<?php

namespace Tests\Unit\Models\Order\Scopes;

use App\Models\Order;
use App\Models\Order\Scopes\ByActiveOrders;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ByActiveOrdersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_active_orders()
    {
        $supplier = Supplier::factory()->createQuietly();
        Order::factory()->usingSupplier($supplier)->completed()->create();
        Order::factory()->usingSupplier($supplier)->canceled()->create();

        $approvedOrder = Order::factory()->usingSupplier($supplier)->approved()->create();
        $pendingOrder  = Order::factory()->usingSupplier($supplier)->pending()->create();

        $expected          = Collection::make([$approvedOrder, $pendingOrder]);
        $expectedRouteKeys = $expected->pluck(Order::keyName());

        $filtered          = Order::scoped(new ByActiveOrders())->get();
        $filteredRouteKeys = $filtered->pluck(Order::keyName());

        $this->assertCount(2, $filtered);
        $this->assertEqualsCanonicalizing($expectedRouteKeys, $filteredRouteKeys);
    }
}

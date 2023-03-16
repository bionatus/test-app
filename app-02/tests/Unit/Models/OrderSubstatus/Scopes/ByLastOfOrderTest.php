<?php

namespace Tests\Unit\Models\OrderSubstatus\Scopes;

use App\Models\Order;
use App\Models\OrderSubstatus;
use App\Models\OrderSubstatus\Scopes\ByLastOfOrder;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByLastOfOrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_the_last_record_that_has_an_order_id()
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();
        OrderSubstatus::factory()->usingOrder($order)->count(5)->create();
        $status = OrderSubstatus::factory()->usingOrder($order)->create();
        OrderSubstatus::factory()->count(5)->create();

        $last = OrderSubstatus::scoped(new ByLastOfOrder($order->getKey()))->get();
        $this->assertCount(1, $last);

        $lastStatus = $last->first();

        $this->assertSame($status->getKey(), $lastStatus->getKey());
    }
}

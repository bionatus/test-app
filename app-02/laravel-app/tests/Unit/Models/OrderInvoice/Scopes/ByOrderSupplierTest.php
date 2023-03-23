<?php

namespace Tests\Unit\Models\OrderInvoice\Scopes;

use App\Models\Order;
use App\Models\OrderInvoice;
use App\Models\OrderInvoice\Scopes\ByOrderSupplier;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ByOrderSupplierTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_order_supplier()
    {
        $supplier              = Supplier::factory()->createQuietly();
        $orders                = Order::factory()->usingSupplier($supplier)->count(3)->createQuietly();
        $expectedOrderInvoices = Collection::make([]);
        $orders->each(function(Order $order) use (&$expectedOrderInvoices) {
            $expectedOrderInvoices->push(OrderInvoice::factory()->usingOrder($order)->create());
        });
        OrderInvoice::factory()->count(2)->createQuietly();

        $filtered = OrderInvoice::scoped(new ByOrderSupplier($orders->first()))->get();

        $this->assertCount($expectedOrderInvoices->count(), $filtered);
        $expectedOrderInvoices->each(function(OrderInvoice $orderInvoice) use ($filtered) {
            $this->assertTrue($filtered->contains($orderInvoice));
        });
    }
}

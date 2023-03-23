<?php

namespace Tests\Unit\Models\OrderInvoice\Scopes;

use App\Models\Order;
use App\Models\OrderInvoice;
use App\Models\OrderInvoice\Scopes\Processed;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessedTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_processed()
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();
        $expected = OrderInvoice::factory()->usingOrder($order)->processed()->count(3)->create();
        OrderInvoice::factory()->usingOrder($order)->notProcessed()->count(2)->create();

        $filtered     = OrderInvoice::scoped(new Processed())->get();
        $expectedKeys = $expected->pluck(OrderInvoice::keyName());
        $filteredKeys = $filtered->pluck(OrderInvoice::keyName());

        $this->assertCount($expected->count(), $filtered);
        $this->assertEqualsCanonicalizing($expectedKeys, $filteredKeys);
    }
}

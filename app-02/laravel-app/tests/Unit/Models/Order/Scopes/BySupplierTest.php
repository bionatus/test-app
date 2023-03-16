<?php

namespace Tests\Unit\Models\Order\Scopes;

use App\Models\Order;
use App\Models\Order\Scopes\BySupplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BySupplierTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_supplier()
    {
        $order = Order::factory()->createQuietly();
        Order::factory()->count(10)->createQuietly();

        $filtered = Order::scoped(new BySupplier($order->supplier))->get();

        $this->assertCount(1, $filtered);
        $this->assertInstanceOf(Order::class, $filtered->first());
        $this->assertSame($order->getKey(), $filtered->first()->getKey());
    }
}

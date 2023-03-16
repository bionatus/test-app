<?php

namespace Tests\Unit\Models\Order\Scopes;

use App\Models\Order;
use App\Models\Order\Scopes\ByUserNotNull;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByUserNotNullTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_user_not_null()
    {
        $supplier = Supplier::factory()->createQuietly();
        $expected = Order::factory()->count(3)->usingSupplier($supplier)->create();
        Order::factory()->usingSupplier($supplier)->count(2)->create(['user_id' => null]);

        $filtered = Order::scoped(new ByUserNotNull())->get();

        $this->assertCount(3, $filtered);
        $filtered->each(function(Order $order) use ($expected) {
            $this->assertSame($expected->shift()->getKey(), $order->getKey());
        });
    }
}

<?php

namespace Tests\Unit\Models\Order\Scopes;

use App\Models\Order;
use App\Models\Order\Scopes\ByUserTimezone;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByUserTimezoneTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_orders_having_users_with_a_timezone()
    {
        $supplier = Supplier::factory()->createQuietly();
        Order::factory()->usingSupplier($supplier)->count(10)->create();
        $user     = User::factory()->create(['timezone' => $timezone = 'a timezone']);
        $expected = Order::factory()->usingSupplier($supplier)->usingUser($user)->count(5)->create();

        $filtered = Order::scoped(new ByUserTimezone($timezone))->get();

        $this->assertCount(5, $filtered);
        $filtered->each(function(Order $order) use ($expected) {
            $this->assertSame($expected->shift()->getKey(), $order->getKey());
        });
    }

    /** @test */
    public function it_filters_by_orders_having_users_without_timezone()
    {
        $supplier = Supplier::factory()->createQuietly();
        Order::factory()->usingSupplier($supplier)->count(2)->create();
        $user = User::factory()->create(['timezone' => 'a timezone']);
        Order::factory()->usingSupplier($supplier)->usingUser($user)->count(3)->create();

        $filtered = Order::scoped(new ByUserTimezone(null))->get();

        $this->assertCount(2, $filtered);
    }
}

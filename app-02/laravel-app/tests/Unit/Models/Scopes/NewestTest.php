<?php

namespace Tests\Unit\Models\Scopes;

use App\Models\OemDetailCounter;
use App\Models\Order;
use App\Models\Scopes\Newest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Tests\TestCase;

class NewestTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function it_orders_by_newest_order()
    {
        $orders = collect([
            Order::factory()->createQuietly(['created_at' => $this->faker->dateTimeBetween('-2 day')]),
            Order::factory()->createQuietly(['created_at' => $this->faker->dateTimeBetween('-1 day')]),
            Order::factory()->createQuietly(['created_at' => $this->faker->dateTimeBetween('-3 day')]),
        ])->sortByDesc('created_at');

        $sorted = Order::scoped(new Newest())->get();

        $sorted->each(function(Order $order) use ($orders) {
            $this->assertSame($orders->shift()->getKey(), $order->getKey());
        });
    }

    /** @test */
    public function it_orders_by_newest_oem_recently_viewed()
    {
        $oems = Collection::make([
            OemDetailCounter::factory()->create(['created_at' => $this->faker->dateTimeBetween('-2 day')]),
            OemDetailCounter::factory()->create(['created_at' => $this->faker->dateTimeBetween('-1 day')]),
            OemDetailCounter::factory()->create(['created_at' => $this->faker->dateTimeBetween('-3 day')]),
        ])->sortByDesc('created_at');

        $sorted = OemDetailCounter::scoped(new Newest())->get();

        $sorted->each(function(OemDetailCounter $oem) use ($oems) {
            $this->assertSame($oems->shift()->getKey(), $oem->getKey());
        });
    }
}

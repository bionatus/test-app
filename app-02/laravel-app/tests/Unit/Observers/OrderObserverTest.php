<?php

namespace Tests\Unit\Observers;

use App\Models\Order;
use App\Observers\OrderObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JMac\Testing\Traits\AdditionalAssertions;
use Tests\TestCase;

class OrderObserverTest extends TestCase
{
    use RefreshDatabase;
    use AdditionalAssertions;

    /** @test */
    public function it_fills_uuid_when_creating()
    {
        $order = Order::factory()->make(['uuid' => null]);

        $observer = new OrderObserver();

        $observer->creating($order);

        $this->assertNotNull($order->uuid);
    }
}

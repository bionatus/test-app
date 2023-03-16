<?php

namespace Tests\Unit\Observers;

use App\Models\OrderDelivery;
use App\Observers\OrderDeliveryObserver;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mockery;
use Tests\TestCase;

class OrderDeliveryObserverTest extends TestCase
{
    /** @test */
    public function it_calls_order_touch_method_when_an_order_delivery_is_saved()
    {
        $belongsTo = Mockery::mock(BelongsTo::class);
        $belongsTo->shouldReceive('touch')->withNoArgs()->once();

        $orderDelivery = Mockery::mock(OrderDelivery::class);
        $orderDelivery->shouldReceive('order')->withNoArgs()->once()->andReturn($belongsTo);

        $observer = new OrderDeliveryObserver();

        $observer->saved($orderDelivery);
    }
}

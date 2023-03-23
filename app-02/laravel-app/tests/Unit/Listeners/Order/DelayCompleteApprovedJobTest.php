<?php

namespace Tests\Unit\Listeners\Order;

use App;
use App\Events\Order\LegacyApproved;
use App\Jobs\Order\CompleteApproved;
use App\Listeners\Order\DelayCompleteApprovedJob;
use App\Models\Order;
use App\Models\OrderDelivery;
use Bus;
use Carbon\CarbonImmutable;
use Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use ReflectionClass;
use ReflectionProperty;
use Tests\TestCase;

class DelayCompleteApprovedJobTest extends TestCase
{
    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(DelayCompleteApprovedJob::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_dispatches_a_delayed_job_if_order_delivery_is_not_curri()
    {
        Config::set('order.autocomplete.ttl', $ttl = 10);
        $now   = CarbonImmutable::now();
        $delay = $now->addMinutes($ttl);

        Bus::fake([CompleteApproved::class]);

        $orderDelivery = Mockery::mock(OrderDelivery::class);
        $orderDelivery->shouldReceive('isCurriDelivery')->withNoArgs()->once()->andReturnFalse();

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAttribute')->with('updated_at')->once()->andReturn($now);
        $order->shouldReceive('getAttribute')->with('orderDelivery')->once()->andReturn($orderDelivery);

        $event = Mockery::mock(LegacyApproved::class);
        $event->shouldReceive('order')->withNoArgs()->once()->andReturn($order);

        App::make(DelayCompleteApprovedJob::class)->handle($event);

        Bus::assertDispatched(CompleteApproved::class, function(CompleteApproved $job) use ($order, $delay, $now) {
            $property = new ReflectionProperty($job, 'order');
            $property->setAccessible(true);
            $this->assertSame($order, $property->getValue($job));

            $this->assertTrue($job->delay->eq($delay));

            return true;
        });
    }

    /** @test */
    public function it_does_not_dispatch_a_delayed_job_if_order_delivery_is_curri()
    {
        Bus::fake([CompleteApproved::class]);

        $orderDelivery = Mockery::mock(OrderDelivery::class);
        $orderDelivery->shouldReceive('isCurriDelivery')->withNoArgs()->once()->andReturnTrue();

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAttribute')->with('orderDelivery')->once()->andReturn($orderDelivery);

        $event = Mockery::mock(LegacyApproved::class);
        $event->shouldReceive('order')->withNoArgs()->once()->andReturn($order);

        App::make(DelayCompleteApprovedJob::class)->handle($event);

        Bus::assertNotDispatched(CompleteApproved::class);
    }
}

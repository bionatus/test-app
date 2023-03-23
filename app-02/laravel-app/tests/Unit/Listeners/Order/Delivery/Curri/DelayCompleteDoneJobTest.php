<?php

namespace Tests\Unit\Listeners\Order\Delivery\Curri;

use App;
use App\Events\Order\Delivery\Curri\ArrivedAtDestination;
use App\Jobs\Order\DelayComplete;
use App\Listeners\Order\Delivery\Curri\DelayCompleteDoneJob;
use App\Models\CurriDelivery;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderSubstatus;
use App\Models\Substatus;
use Bus;
use Carbon\CarbonImmutable;
use Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use ReflectionClass;
use ReflectionProperty;
use Tests\TestCase;

class DelayCompleteDoneJobTest extends TestCase
{
    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(DelayCompleteDoneJob::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_dispatches_a_delayed_job_if_order_delivery_is_curri_and_substatus_is_approved_delivered()
    {
        Config::set('order.autocomplete.curri_ttl', $ttl = 10);
        $now   = CarbonImmutable::now();
        $delay = $now->addMinutes($ttl);

        Bus::fake(DelayComplete::class);

        $curriDelivery = Mockery::mock(CurriDelivery::class);
        $curriDelivery->shouldReceive('getAttribute')->with('updated_at')->once()->andReturn($now);

        $orderDelivery = Mockery::mock(OrderDelivery::class);
        $orderDelivery->shouldReceive('isCurriDelivery')->withNoArgs()->once()->andReturnTrue();
        $orderDelivery->shouldReceive('getAttribute')->with('deliverable')->once()->andReturn($curriDelivery);

        $approvedDelivered = Substatus::STATUS_APPROVED_DELIVERED;

        $lastStatus = Mockery::mock(OrderSubstatus::class);
        $lastStatus->shouldReceive('getAttribute')->with('substatus_id')->once()->andReturn($approvedDelivered);

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getKey')->withNoArgs()->andReturn(1);
        $order->shouldReceive('getAttribute')->with('orderDelivery')->once()->andReturn($orderDelivery);
        $order->shouldReceive('getAttribute')->with('lastStatus')->once()->andReturn($lastStatus);

        $event = Mockery::mock(ArrivedAtDestination::class);
        $event->shouldReceive('order')->withNoArgs()->once()->andReturn($order);

        App::bind(OrderSubstatus::class, fn() => $lastStatus);

        App::make(DelayCompleteDoneJob::class)->handle($event);

        Bus::assertDispatched(DelayComplete::class, function(DelayComplete $job) use ($order, $delay, $now) {
            $property = new ReflectionProperty($job, 'order');

            $this->assertSame($order, $property->getValue($job));

            $this->assertTrue($job->delay->eq($delay));

            return true;
        });
    }

    /** @test */
    public function it_does_not_dispatch_a_delayed_job_if_order_delivery_is_not_curri()
    {
        Bus::fake(DelayComplete::class);

        $orderDelivery = Mockery::mock(OrderDelivery::class);
        $orderDelivery->shouldReceive('isCurriDelivery')->withNoArgs()->once()->andReturnFalse();

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAttribute')->with('orderDelivery')->once()->andReturn($orderDelivery);

        $event = Mockery::mock(ArrivedAtDestination::class);
        $event->shouldReceive('order')->withNoArgs()->once()->andReturn($order);

        App::make(DelayCompleteDoneJob::class)->handle($event);

        Bus::assertNotDispatched(DelayComplete::class);
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_does_not_dispatch_a_delayed_job_if_substatus_is_not_approved_delivered(int $substatusId)
    {
        Bus::fake(DelayComplete::class);

        $orderDelivery = Mockery::mock(OrderDelivery::class);
        $orderDelivery->shouldReceive('isCurriDelivery')->withNoArgs()->once()->andReturnTrue();

        $lastStatus = Mockery::mock(OrderSubstatus::class);
        $lastStatus->shouldReceive('getAttribute')->with('substatus_id')->once()->andReturn($substatusId);

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAttribute')->with('orderDelivery')->once()->andReturn($orderDelivery);
        $order->shouldReceive('getAttribute')->with('lastStatus')->once()->andReturn($lastStatus);

        $event = Mockery::mock(ArrivedAtDestination::class);
        $event->shouldReceive('order')->withNoArgs()->once()->andReturn($order);

        App::make(DelayCompleteDoneJob::class)->handle($event);

        Bus::assertNotDispatched(DelayComplete::class);
    }

    public function dataProvider(): array
    {
        return [
            [Substatus::STATUS_PENDING_REQUESTED],
            [Substatus::STATUS_PENDING_ASSIGNED],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED],
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY],
            [Substatus::STATUS_APPROVED_READY_FOR_DELIVERY],
            [Substatus::STATUS_COMPLETED_DONE],
            [Substatus::STATUS_CANCELED_DECLINED],
            [Substatus::STATUS_CANCELED_CANCELED],
            [Substatus::STATUS_CANCELED_ABORTED],
            [Substatus::STATUS_CANCELED_REJECTED],
        ];
    }
}

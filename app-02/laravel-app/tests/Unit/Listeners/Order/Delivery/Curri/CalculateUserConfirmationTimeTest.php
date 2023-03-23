<?php

namespace Tests\Unit\Listeners\Order\Delivery\Curri;

use App\Events\Order\OrderEventInterface;
use App\Jobs\Order\Delivery\Curri\DispatchUserConfirmationRequired as DispatchUserConfirmationRequiredJob;
use App\Listeners\Order\Delivery\Curri\CalculateUserConfirmationTime;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Supplier;
use Bus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Carbon;
use Mockery;
use ReflectionClass;
use Tests\CanRefreshDatabase;
use Tests\TestCase;

class CalculateUserConfirmationTimeTest extends TestCase
{
    use CanRefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(CalculateUserConfirmationTime::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_dispatches_a_dispatch_user_confirmation_required_job_if_order_delivery_is_curri_and_approved()
    {
        $this->refreshDatabaseForSingleTest();
        Bus::fake(DispatchUserConfirmationRequiredJob::class);

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getAttribute')->with('timezone')->andReturn('America/Los_Angeles');

        $orderDelivery = Mockery::mock(OrderDelivery::class);
        $orderDelivery->shouldReceive('getAttribute')->with('date')->andReturn(Carbon::now());
        $orderDelivery->shouldReceive('getAttribute')->with('start_time')->andReturn(Carbon::createFromTime(9));
        $orderDelivery->shouldReceive('getAttribute')->with('end_time')->andReturn(Carbon::createFromTime(17));
        $orderDelivery->shouldReceive('isCurriDelivery')->withNoArgs()->andReturnTrue();

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getKey')->withNoArgs()->andReturn(1);
        $order->shouldReceive('getAttribute')->with('supplier')->andReturn($supplier);
        $order->shouldReceive('getAttribute')->with('orderDelivery')->andReturn($orderDelivery);
        $order->shouldReceive('isApproved')->once()->andReturnTrue();

        $listener = new CalculateUserConfirmationTime();
        $listener->handle($this->orderEventStub($order));

        Bus::assertDispatched(DispatchUserConfirmationRequiredJob::class, fn($job) => !is_null($job->delay));
    }

    /** @test */
    public function it_does_not_dispatch_a_dispatch_user_confirmation_required_job_if_order_delivery_is_not_curri()
    {
        Bus::fake(DispatchUserConfirmationRequiredJob::class);

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getAttribute')->with('timezone')->andReturn('America/Los_Angeles');

        $orderDelivery = Mockery::mock(OrderDelivery::class);
        $orderDelivery->shouldReceive('getAttribute')->with('date')->andReturn(Carbon::now());
        $orderDelivery->shouldReceive('getAttribute')->with('start_time')->andReturn(Carbon::createFromTime(9));
        $orderDelivery->shouldReceive('getAttribute')->with('end_time')->andReturn(Carbon::createFromTime(17));
        $orderDelivery->shouldReceive('isCurriDelivery')->withNoArgs()->andReturnFalse();

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAttribute')->with('supplier')->andReturn($supplier);
        $order->shouldReceive('getAttribute')->with('orderDelivery')->andReturn($orderDelivery);
        $order->shouldReceive('isApproved')->withNoArgs()->andReturnTrue();

        $listener = new CalculateUserConfirmationTime();
        $listener->handle($this->orderEventStub($order));

        Bus::assertNotDispatched(DispatchUserConfirmationRequiredJob::class);
    }

    /** @test */
    public function it_does_not_dispatch_a_dispatch_user_confirmation_required_job_if_order_delivery_is_not_approved()
    {
        Bus::fake(DispatchUserConfirmationRequiredJob::class);

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getAttribute')->with('timezone')->andReturn('America/Los_Angeles');

        $orderDelivery = Mockery::mock(OrderDelivery::class);
        $orderDelivery->shouldReceive('getAttribute')->with('date')->andReturn(Carbon::now());
        $orderDelivery->shouldReceive('getAttribute')->with('start_time')->andReturn(Carbon::createFromTime(9));
        $orderDelivery->shouldReceive('getAttribute')->with('end_time')->andReturn(Carbon::createFromTime(17));
        $orderDelivery->shouldReceive('isCurriDelivery')->withNoArgs()->andReturnTrue();

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAttribute')->with('supplier')->andReturn($supplier);
        $order->shouldReceive('getAttribute')->with('orderDelivery')->andReturn($orderDelivery);
        $order->shouldReceive('isApproved')->withNoArgs()->andReturnFalse();

        $listener = new CalculateUserConfirmationTime();
        $listener->handle($this->orderEventStub($order));

        Bus::assertNotDispatched(DispatchUserConfirmationRequiredJob::class);
    }

    /** @test */
    public function it_dispatches_only_once_dispatch_user_confirmation_required_job_if_the_date_and_time_is_the_same()
    {
        $this->refreshDatabaseForSingleTest();
        Bus::fake(DispatchUserConfirmationRequiredJob::class);

        $supplier = Mockery::mock(Supplier::class);
        $supplier->shouldReceive('getAttribute')->with('timezone')->andReturn('America/Los_Angeles');

        $orderDelivery = Mockery::mock(OrderDelivery::class);
        $orderDelivery->shouldReceive('getAttribute')->with('date')->andReturn(Carbon::now());
        $orderDelivery->shouldReceive('getAttribute')->with('start_time')->andReturn(Carbon::createFromTime(9));
        $orderDelivery->shouldReceive('getAttribute')->with('end_time')->andReturn(Carbon::createFromTime(17));
        $orderDelivery->shouldReceive('isCurriDelivery')->withNoArgs()->andReturnTrue();

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getKey')->withNoArgs()->andReturn(1);
        $order->shouldReceive('getAttribute')->with('supplier')->andReturn($supplier);
        $order->shouldReceive('getAttribute')->with('orderDelivery')->andReturn($orderDelivery);
        $order->shouldReceive('isApproved')->twice()->andReturnTrue();

        $listener = new CalculateUserConfirmationTime();
        $listener->handle($this->orderEventStub($order));

        $listener2 = new CalculateUserConfirmationTime();
        $listener2->handle($this->orderEventStub($order));

        Bus::assertDispatchedTimes(DispatchUserConfirmationRequiredJob::class, 1);
    }

    private function orderEventStub(Order $order): OrderEventInterface
    {
        return new class($order) implements OrderEventInterface {
            private Order $order;

            public function __construct(Order $order)
            {
                $this->order = $order;
            }

            public function order(): Order
            {
                return $this->order;
            }
        };
    }
}

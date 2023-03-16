<?php

namespace Tests\Unit\Listeners\Order\Delivery\Curri;

use App\Events\Order\OrderEventInterface;
use App\Jobs\Order\Delivery\Curri\RemoveUserDeliveryInformation as RemoveDeliveryInformationJob;
use App\Listeners\Order\Delivery\Curri\RemoveUserDeliveryInformation;
use App\Models\Order;
use App\Models\OrderDelivery;
use Bus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class RemoveUserDeliveryInformationTest extends TestCase
{
    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(RemoveUserDeliveryInformation::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_dispatches_a_set_curri_delivery_information_job_if_order_delivery_is_curri()
    {
        Bus::fake(RemoveDeliveryInformationJob::class);

        $orderDelivery = Mockery::mock(OrderDelivery::class);
        $orderDelivery->shouldReceive('isCurriDelivery')->once()->andReturnTrue();

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAttribute')->with('orderDelivery')->once()->andReturn($orderDelivery);

        (new RemoveUserDeliveryInformation())->handle($this->orderEventStub($order));

        Bus::assertDispatched(RemoveDeliveryInformationJob::class);
    }

    /** @test */
    public function it_does_not_dispatch_a_set_curri_delivery_information_job_if_order_delivery_is_not_curri()
    {
        Bus::fake(RemoveDeliveryInformationJob::class);

        $orderDelivery = Mockery::mock(OrderDelivery::class);
        $orderDelivery->shouldReceive('isCurriDelivery')->once()->andReturnFalse();

        $order = Mockery::mock(Order::class);
        $order->shouldReceive('getAttribute')->with('orderDelivery')->once()->andReturn($orderDelivery);

        (new RemoveUserDeliveryInformation())->handle($this->orderEventStub($order));

        Bus::assertNotDispatched(RemoveDeliveryInformationJob::class);
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

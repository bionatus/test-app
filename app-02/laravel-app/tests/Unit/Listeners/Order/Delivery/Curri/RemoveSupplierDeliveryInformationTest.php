<?php

namespace Tests\Unit\Listeners\Order\Delivery\Curri;

use App\Events\Order\OrderEventInterface;
use App\Jobs\Order\Delivery\Curri\RemoveSupplierDeliveryInformation as RemoveSupplierDeliveryInformationJob;
use App\Listeners\Order\Delivery\Curri\RemoveSupplierDeliveryInformation;
use App\Models\Order;
use Bus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class RemoveSupplierDeliveryInformationTest extends TestCase
{
    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(RemoveSupplierDeliveryInformation::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_dispatches_a_remove_supplier_delivery_information_job()
    {
        Bus::fake();

        $order = Mockery::mock(Order::class);

        (new RemoveSupplierDeliveryInformation())->handle($this->orderEventStub($order));

        Bus::assertDispatched(RemoveSupplierDeliveryInformationJob::class);
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

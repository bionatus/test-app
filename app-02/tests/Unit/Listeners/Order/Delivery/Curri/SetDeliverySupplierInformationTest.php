<?php

namespace Tests\Unit\Listeners\Order\Delivery\Curri;

use App\Events\Order\OrderEventInterface;
use App\Jobs\Order\Delivery\Curri\SetDeliverySupplierInformation as SetDeliverySupplierInformationJob;
use App\Listeners\Order\Delivery\Curri\SetDeliverySupplierInformation;
use App\Models\Order;
use Bus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class SetDeliverySupplierInformationTest extends TestCase
{
    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SetDeliverySupplierInformation::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_dispatches_a_set_curri_delivery_information_to_supplier_job()
    {
        Bus::fake();

        $order = Mockery::mock(Order::class);

        $listener = new SetDeliverySupplierInformation();
        $listener->handle($this->orderEventStub($order));

        Bus::assertDispatched(SetDeliverySupplierInformationJob::class);
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

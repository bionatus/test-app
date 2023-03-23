<?php

namespace Tests\Unit\Listeners\OrderSnap;

use App\Events\Order\OrderEventInterface;
use App\Jobs\OrderSnap\SaveOrderSnapInformation as SaveOrderSnapInformationJob;
use App\Listeners\Order\Delivery\Curri\SetUserDeliveryInformation;
use App\Listeners\OrderSnap\SaveOrderSnapInformation;
use App\Models\Order;
use Bus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class SaveOrderSnapInformationTest extends TestCase
{
    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SetUserDeliveryInformation::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_dispatches_a_save_order_snap_information_job()
    {
        Bus::fake();

        $order = Mockery::mock(Order::class);

        $listener = new SaveOrderSnapInformation();
        $listener->handle($this->orderEventStub($order));

        Bus::assertDispatched(SaveOrderSnapInformationJob::class);
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

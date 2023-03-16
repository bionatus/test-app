<?php

namespace Tests\Unit\Handler\OrderSubstatus;

use App;
use App\Handlers\OrderSubstatus\OrderSubstatusPickupHandler;
use App\Handlers\OrderSubstatus\OrderSubstatusUpdated;
use App\Jobs\Order\Delivery\Pickup\DelayApprovedReadyForDelivery;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderSubstatus;
use App\Models\Pickup;
use App\Models\Substatus;
use App\Models\Supplier;
use Bus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

class OrderSubstatusPickupHandlerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_implements_is_order_substatus_changed_interface()
    {
        $reflection = new ReflectionClass(OrderSubstatusPickupHandler::class);

        $this->assertTrue($reflection->implementsInterface(OrderSubstatusUpdated::class));
    }

    /** @test
     * @dataProvider orderDeliveryProvider
     */
    public function it_updates_an_order_substatus_to_awaiting_delivery_or_ready_for_delivery(
        bool $isNeededNow,
        int $minutes,
        int $expectedId
    ) {
        Carbon::setTestNow('2023-03-23 13:00:00');
        $order         = Order::factory()->createQuietly();
        $deliveryTime  = Carbon::now()->addMinutes($minutes);
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->pickup()->create([
            'date'          => $deliveryTime->format('Y-m-d'),
            'start_time'    => $deliveryTime->format('H:i'),
            'is_needed_now' => $isNeededNow,
        ]);

        Pickup::factory()->usingOrderDelivery($orderDelivery)->create();
        OrderSubstatus::factory()
            ->usingOrder($order)
            ->usingSubstatusId(Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED)
            ->create();

        $handler  = App::make(OrderSubstatusPickupHandler::class);
        $newOrder = $handler->processPendingApprovalQuoteNeeded($order);

        $this->assertEquals($expectedId, $newOrder->lastStatus->substatus_id);
    }

    public function orderDeliveryProvider(): array
    {
        return [
            //isNeededNow, minutes, $expectedId
            [true, 30, Substatus::STATUS_APPROVED_READY_FOR_DELIVERY],
            [true, 100, Substatus::STATUS_APPROVED_READY_FOR_DELIVERY],
            [false, 30, Substatus::STATUS_APPROVED_READY_FOR_DELIVERY],
            [false, 100, Substatus::STATUS_APPROVED_AWAITING_DELIVERY],
        ];
    }

    /** @test */
    public function it_dispatch_the_delay_approved_ready_for_delivery_job_when_requirements_satisfy()
    {
        Bus::fake();

        Carbon::setTestNow('2022-11-10 04:12AM');

        $supplier = Supplier::factory()->createQuietly(['timezone' => 'UTC']);
        $order    = Order::factory()->usingSupplier($supplier)->pendingApproval()->create();

        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->pickup()->create([
            'date'          => Carbon::now(),
            'start_time'    => Carbon::createFromTime(15)->format('H:i'),
            'end_time'      => Carbon::createFromTime(18)->format('H:i'),
            'fee'           => 1000,
            'is_needed_now' => false,
        ]);
        Pickup::factory()->usingOrderDelivery($orderDelivery)->create();
        OrderSubstatus::factory()
            ->usingOrder($order)
            ->usingSubstatusId(Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED)
            ->create();

        $handler = App::make(OrderSubstatusPickupHandler::class);
        $handler->processPendingApprovalQuoteNeeded($order);

        Bus::assertDispatched(DelayApprovedReadyForDelivery::class);
    }
}

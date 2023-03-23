<?php

namespace Tests\Unit\Policies\LiveApi\V1\Order;

use App\Models\CurriDelivery;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderSubstatus;
use App\Models\Staff;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Policies\LiveApi\V1\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class UpdateInProgressDeliveryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_disallows_another_processor_to_update_an_order()
    {
        $notProcessor = Staff::factory()->createQuietly();
        $order        = Order::factory()->approved()->createQuietly();
        OrderDelivery::factory()->pickup()->usingOrder($order)->create();

        $policy = new OrderPolicy();

        $this->assertFalse($policy->updateInProgressDelivery($notProcessor, $order));
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_disallows_the_processor_to_update_a_non_approved_order(
        int $substatusId,
        bool $expectedResult
    ) {
        $processor = Staff::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($processor->supplier)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();
        OrderDelivery::factory()->pickup()->usingOrder($order)->create();

        $policy = new OrderPolicy();

        $this->assertEquals($expectedResult, $policy->updateInProgressDelivery($processor, $order));
    }

    public function dataProvider(): array
    {
        return [
            [Substatus::STATUS_PENDING_REQUESTED, false],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, false],
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY, true],
            [Substatus::STATUS_COMPLETED_DONE, false],
            [Substatus::STATUS_CANCELED_REJECTED, false],
        ];
    }

    /**
     * @test
     * @dataProvider timeProvider
     */
    public function it_disallows_the_processor_to_update_a_curri_delivery_when_the_user_confirmation_was_shown(
        string $nowTime,
        string $deliveryTye,
        bool $expected
    ) {
        Carbon::setTestNow("2022-11-22 $nowTime");

        $supplier      = Supplier::factory()->createQuietly(['timezone' => 'America/Los_Angeles']);
        $staff         = Staff::factory()->usingSupplier($supplier)->create();
        $order         = Order::factory()->usingSupplier($supplier)->approved()->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->create([
            'type'       => $deliveryTye,
            'date'       => Carbon::now()->format('Y-m-d'),
            'start_time' => Carbon::createFromTime(9)->format('H:i'),
        ]);
        if ($deliveryTye == OrderDelivery::TYPE_CURRI_DELIVERY) {
            CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();
        }

        $policy = new OrderPolicy();

        $this->assertEquals($expected, $policy->updateInProgressDelivery($staff, $order));
    }

    public function timeProvider(): array
    {
        return [
            // start_date | start_time | deliver type | expected
            ['16:30:00', OrderDelivery::TYPE_OTHER_DELIVERY, true],
            ['16:30:00', OrderDelivery::TYPE_PICKUP, true],
            ['16:30:00', OrderDelivery::TYPE_SHIPMENT_DELIVERY, true],
            ['16:30:00', OrderDelivery::TYPE_WAREHOUSE_DELIVERY, true],
            ['16:30:00', OrderDelivery::TYPE_SHIPMENT_DELIVERY, true],
            ['16:30:00', OrderDelivery::TYPE_CURRI_DELIVERY, false],
            ['16:29:00', OrderDelivery::TYPE_CURRI_DELIVERY, true],
        ];
    }
}

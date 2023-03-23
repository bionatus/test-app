<?php

namespace Tests\Unit\Policies\LiveApi\V2\Order;

use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderSubstatus;
use App\Models\Staff;
use App\Models\Substatus;
use App\Policies\LiveApi\V2\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancelInProgressTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_disallows_another_processor_to_cancel_an_order()
    {
        $notProcessor = Staff::factory()->createQuietly();
        $order        = Order::factory()->approved()->createQuietly();
        OrderDelivery::factory()->usingOrder($order)->pickup()->create();

        $policy = new OrderPolicy();

        $this->assertFalse($policy->cancelInProgress($notProcessor, $order));
    }

    /**
     * @test
     * @dataProvider pickupDataProvider
     */
    public function it_disallows_the_owner_to_cancel_a_pickup_order(
        int $substatusId,
        bool $expectedResult
    ) {
        $processor = Staff::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($processor->supplier)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();
        OrderDelivery::factory()->pickup()->usingOrder($order)->create();

        $policy = new OrderPolicy();

        $this->assertEquals($expectedResult, $policy->cancelInProgress($processor, $order));
    }

    public function pickupDataProvider(): array
    {
        return [
            [Substatus::STATUS_PENDING_REQUESTED, false],
            [Substatus::STATUS_PENDING_ASSIGNED, false],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, false],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED, true],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED, false],
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY, true],
            [Substatus::STATUS_APPROVED_READY_FOR_DELIVERY, false],
            [Substatus::STATUS_APPROVED_DELIVERED, false],
            [Substatus::STATUS_COMPLETED_DONE, false],
            [Substatus::STATUS_CANCELED_ABORTED, false],
            [Substatus::STATUS_CANCELED_CANCELED, false],
            [Substatus::STATUS_CANCELED_DECLINED, false],
            [Substatus::STATUS_CANCELED_REJECTED, false],
            [Substatus::STATUS_CANCELED_BLOCKED_USER, false],
            [Substatus::STATUS_CANCELED_DELETED_USER, false],
        ];
    }

    /**
     * @test
     * @dataProvider curriDeliveryDataProvider
     */
    public function it_disallows_the_owner_to_cancel_a_curri_delivery_order(
        int $substatusId,
        bool $expectedResult
    ) {
        $processor = Staff::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($processor->supplier)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();
        OrderDelivery::factory()->curriDelivery()->usingOrder($order)->create();

        $policy = new OrderPolicy();

        $this->assertEquals($expectedResult, $policy->cancelInProgress($processor, $order));
    }

    public function curriDeliveryDataProvider(): array
    {
        return [
            [Substatus::STATUS_PENDING_REQUESTED, false],
            [Substatus::STATUS_PENDING_ASSIGNED, false],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, false],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED, true],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED, false],
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY, true],
            [Substatus::STATUS_APPROVED_READY_FOR_DELIVERY, false],
            [Substatus::STATUS_APPROVED_DELIVERED, false],
            [Substatus::STATUS_COMPLETED_DONE, false],
            [Substatus::STATUS_CANCELED_ABORTED, false],
            [Substatus::STATUS_CANCELED_CANCELED, false],
            [Substatus::STATUS_CANCELED_DECLINED, false],
            [Substatus::STATUS_CANCELED_REJECTED, false],
            [Substatus::STATUS_CANCELED_BLOCKED_USER, false],
            [Substatus::STATUS_CANCELED_DELETED_USER, false],
        ];
    }

    /**
     * @test
     * @dataProvider shipmentDeliveryDataProvider
     */
    public function it_disallows_the_owner_to_cancel_a_shipment_delivery_order(
        int $substatusId,
        bool $expectedResult
    ) {
        $processor = Staff::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($processor->supplier)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();
        OrderDelivery::factory()->shipmentDelivery()->usingOrder($order)->create();

        $policy = new OrderPolicy();

        $this->assertEquals($expectedResult, $policy->cancelInProgress($processor, $order));
    }

    public function shipmentDeliveryDataProvider(): array
    {
        return [
            [Substatus::STATUS_PENDING_REQUESTED, false],
            [Substatus::STATUS_PENDING_ASSIGNED, false],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, false],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED, true],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED, true],
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY, true],
            [Substatus::STATUS_APPROVED_READY_FOR_DELIVERY, false],
            [Substatus::STATUS_APPROVED_DELIVERED, false],
            [Substatus::STATUS_COMPLETED_DONE, false],
            [Substatus::STATUS_CANCELED_ABORTED, false],
            [Substatus::STATUS_CANCELED_CANCELED, false],
            [Substatus::STATUS_CANCELED_DECLINED, false],
            [Substatus::STATUS_CANCELED_REJECTED, false],
            [Substatus::STATUS_CANCELED_BLOCKED_USER, false],
            [Substatus::STATUS_CANCELED_DELETED_USER, false],
        ];
    }
}

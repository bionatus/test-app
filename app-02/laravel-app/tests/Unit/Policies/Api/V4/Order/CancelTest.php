<?php

namespace Tests\Unit\Policies\Api\V4\Order;

use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderSubstatus;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Models\User;
use App\Policies\Api\V4\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_disallows_another_user_to_cancel_an_order()
    {
        $notOwner = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->pending()->create();

        $policy = new OrderPolicy();

        $this->assertFalse($policy->cancel($notOwner, $order));
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_disallows_the_owner_to_cancel_a_non_pending_or_pending_approval_order(
        int $substatusId,
        bool $expectedResult
    ) {
        $owner    = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingUser($owner)->usingSupplier($supplier)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();

        $policy = new OrderPolicy();

        $this->assertEquals($expectedResult, $policy->cancel($owner, $order));
    }

    public function dataProvider(): array
    {
        return [
            [Substatus::STATUS_PENDING_REQUESTED, true],
            [Substatus::STATUS_PENDING_ASSIGNED, true],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, true],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED, true],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED, true],
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY, false],
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
     * @dataProvider pickupDataProvider
     */
    public function it_disallows_the_owner_to_cancel_a_pickup_order(
        int $substatusId,
        bool $expectedResult
    ) {
        $owner    = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingUser($owner)->usingSupplier($supplier)->create();
        OrderDelivery::factory()->pickup()->usingOrder($order)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();
        $order->fresh();

        $policy = new OrderPolicy();

        $this->assertEquals($expectedResult, $policy->cancel($owner, $order));
    }

    public function pickupDataProvider(): array
    {
        return [
            [Substatus::STATUS_PENDING_REQUESTED, true],
            [Substatus::STATUS_PENDING_ASSIGNED, true],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, false],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED, true],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED, false],
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY, false],
            [Substatus::STATUS_APPROVED_READY_FOR_DELIVERY, false],
            [Substatus::STATUS_APPROVED_DELIVERED, true],
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
        $owner    = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingUser($owner)->usingSupplier($supplier)->create();
        OrderDelivery::factory()->curriDelivery()->usingOrder($order)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();
        $order->fresh();

        $policy = new OrderPolicy();

        $this->assertEquals($expectedResult, $policy->cancel($owner, $order));
    }

    public function curriDeliveryDataProvider(): array
    {
        return [
            [Substatus::STATUS_PENDING_REQUESTED, true],
            [Substatus::STATUS_PENDING_ASSIGNED, true],
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
        $owner    = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingUser($owner)->usingSupplier($supplier)->create();
        OrderDelivery::factory()->shipmentDelivery()->usingOrder($order)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();
        $order->fresh();

        $policy = new OrderPolicy();

        $this->assertEquals($expectedResult, $policy->cancel($owner, $order));
    }

    public function shipmentDeliveryDataProvider(): array
    {
        return [
            [Substatus::STATUS_PENDING_REQUESTED, true],
            [Substatus::STATUS_PENDING_ASSIGNED, true],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, false],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED, true],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED, false],
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY, false],
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

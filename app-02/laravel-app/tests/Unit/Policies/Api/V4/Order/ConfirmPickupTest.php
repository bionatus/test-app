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

class ConfirmPickupTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_disallows_another_user_to_confirm_the_pickup_order()
    {
        $notOwner = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();
        OrderSubstatus::factory()
            ->usingSubstatusId(Substatus::STATUS_APPROVED_AWAITING_DELIVERY)
            ->usingOrder($order)
            ->create();

        $policy = new OrderPolicy();

        $this->assertFalse($policy->confirmPickup($notOwner, $order));
    }

    /** @test */
    public function it_disallows_to_confirm_the_pickup_order_if_the_order_is_not_pickup()
    {
        $owner    = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingUser($owner)->usingSupplier($supplier)->create();
        OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();
        OrderSubstatus::factory()
            ->usingSubstatusId(Substatus::STATUS_APPROVED_AWAITING_DELIVERY)
            ->usingOrder($order)
            ->create();

        $policy = new OrderPolicy();

        $this->assertFalse($policy->confirmPickup($owner, $order));
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_disallows_to_confirm_the_pickup_order_if_the_order_is_not_approved_or_already_was_confirmed(
        $orderStatus,
        $expectedResult
    ) {
        $owner    = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingUser($owner)->usingSupplier($supplier)->create();
        OrderDelivery::factory()->usingOrder($order)->pickup()->create();

        OrderSubstatus::factory()->usingSubstatusId($orderStatus)->usingOrder($order)->create();

        $policy = new OrderPolicy();

        $this->assertEquals($expectedResult, $policy->confirmPickup($owner, $order));
    }

    public function dataProvider(): array
    {
        return [
            [Substatus::STATUS_PENDING_REQUESTED, false],
            [Substatus::STATUS_PENDING_ASSIGNED, false],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, false],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED, false],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED, false],
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY, true],
            [Substatus::STATUS_APPROVED_READY_FOR_DELIVERY, true],
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

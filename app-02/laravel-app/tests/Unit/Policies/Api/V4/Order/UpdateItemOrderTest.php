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

class UpdateItemOrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_disallows_another_user_to_add_a_supply_item_to_the_user_order()
    {
        $notOwner = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();

        $policy = new OrderPolicy();

        $this->assertFalse($policy->updateItemOrder($notOwner, $order));
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_disallows_to_add_a_supply_item_to_the_order_if_the_order_is_not_in_pending_approval_fulfilled(
        int $orderStatus,
        bool $expectedResult
    ) {
        $owner    = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingUser($owner)->usingSupplier($supplier)->create();
        OrderDelivery::factory()->usingOrder($order)->pickup()->create();

        OrderSubstatus::factory()->usingSubstatusId($orderStatus)->usingOrder($order)->create();

        $policy = new OrderPolicy();

        $this->assertEquals($expectedResult, $policy->updateItemOrder($owner, $order));
    }

    public function dataProvider(): array
    {
        return [
            [Substatus::STATUS_PENDING_REQUESTED, false],
            [Substatus::STATUS_PENDING_ASSIGNED, false],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, true],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED, false],
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

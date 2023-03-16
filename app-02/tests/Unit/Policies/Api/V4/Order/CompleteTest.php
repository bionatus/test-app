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

class CompleteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_disallows_another_user_to_complete_an_order()
    {
        $notOwner = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->pending()->create();

        $policy = new OrderPolicy();

        $this->assertFalse($policy->complete($notOwner, $order));
    }

    /** @test */
    public function it_disallows_the_owner_to_complete_an_order_with_pickup()
    {
        $substatusId = Substatus::STATUS_APPROVED_DELIVERED;
        $owner       = User::factory()->create();
        $supplier    = Supplier::factory()->createQuietly();
        $order       = Order::factory()->usingUser($owner)->usingSupplier($supplier)->create();
        OrderDelivery::factory()->pickup()->usingOrder($order)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();

        $policy = new OrderPolicy();

        $this->assertFalse($policy->complete($owner, $order));
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_disallows_the_owner_to_complete_an_non_approved_delivery_order(
        int $substatusId,
        bool $expectedResult
    ) {
        $owner    = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingUser($owner)->usingSupplier($supplier)->create();
        OrderDelivery::factory()->curriDelivery()->usingOrder($order)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();

        $policy = new OrderPolicy();

        $this->assertEquals($expectedResult, $policy->complete($owner, $order));
    }

    /** @test */
    public function it_allows_the_owner_to_complete_an_order_with_curri_delivery()
    {
        $substatusId = Substatus::STATUS_APPROVED_DELIVERED;
        $owner       = User::factory()->create();
        $order       = Order::factory()->usingUser($owner)->create();

        OrderDelivery::factory()->curriDelivery()->usingOrder($order)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();

        $policy = new OrderPolicy();

        $this->assertTrue($policy->complete($owner, $order));
    }

    public function dataProvider(): array
    {
        return [
            [Substatus::STATUS_PENDING_REQUESTED, false],
            [Substatus::STATUS_PENDING_ASSIGNED, false],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, false],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED, false],
            [Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED, false],
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY, false],
            [Substatus::STATUS_APPROVED_DELIVERED, true],
            [Substatus::STATUS_COMPLETED_DONE, false],
            [Substatus::STATUS_CANCELED_REJECTED, false],
        ];
    }
}

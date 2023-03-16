<?php

namespace Tests\Unit\Policies\Api\V4\Order;

use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderSubstatus;
use App\Models\Substatus;
use App\Models\User;
use App\Policies\Api\V4\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateNameTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_the_owner_to_update_the_name_from_an_order_with_curri_delivery()
    {
        $this->owner  = User::factory()->create();
        $orders       = Order::factory()->usingUser($this->owner)->count(5)->create();
        $statuses     = [
            Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED,
            Substatus::STATUS_APPROVED_AWAITING_DELIVERY,
            Substatus::STATUS_APPROVED_READY_FOR_DELIVERY,
            Substatus::STATUS_APPROVED_DELIVERED,
            Substatus::STATUS_COMPLETED_DONE,
        ];
        $this->policy = new OrderPolicy();
        $orders->each(function(Order $order, int $index) use ($statuses) {
            OrderDelivery::factory()->curriDelivery()->usingOrder($order)->create();
            OrderSubstatus::factory()->usingOrder($order)->usingSubstatus(Substatus::find($statuses[$index]))->create();
            $this->assertTrue($this->policy->updateName($this->owner, $order));
        });
    }

    /** @test */
    public function it_allows_the_owner_to_update_the_name_from_an_order_with_pickup_delivery()
    {
        $this->owner  = User::factory()->create();
        $orders       = Order::factory()->usingUser($this->owner)->count(4)->create();
        $statuses     = [
            Substatus::STATUS_APPROVED_AWAITING_DELIVERY,
            Substatus::STATUS_APPROVED_READY_FOR_DELIVERY,
            Substatus::STATUS_APPROVED_DELIVERED,
            Substatus::STATUS_COMPLETED_DONE,
        ];
        $this->policy = new OrderPolicy();
        $orders->each(function(Order $order, int $index) use ($statuses) {
            OrderDelivery::factory()->pickup()->usingOrder($order)->create();
            OrderSubstatus::factory()->usingOrder($order)->usingSubstatus(Substatus::find($statuses[$index]))->create();
            $this->assertTrue($this->policy->updateName($this->owner, $order));
        });
    }

    /** @test */
    public function it_allows_the_owner_to_update_the_name_from_an_order_with_shipment_delivery()
    {
        $this->owner  = User::factory()->create();
        $orders       = Order::factory()->usingUser($this->owner)->count(3)->create();
        $statuses     = [
            Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED,
            Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED,
            Substatus::STATUS_APPROVED_AWAITING_DELIVERY,
            Substatus::STATUS_COMPLETED_DONE,
        ];
        $this->policy = new OrderPolicy();
        $orders->each(function(Order $order, int $index) use ($statuses) {
            OrderDelivery::factory()->shipmentDelivery()->usingOrder($order)->create();
            OrderSubstatus::factory()->usingOrder($order)->usingSubstatus(Substatus::find($statuses[$index]))->create();
            $this->assertTrue($this->policy->updateName($this->owner, $order));
        });
    }

    /** @test */
    public function it_disallows_another_user_to_update_the_name_from_an_order()
    {
        $notOwner = User::factory()->create();
        $order    = Order::factory()->create();
        OrderDelivery::factory()->shipmentDelivery()->usingOrder($order)->create();
        OrderSubstatus::factory()
            ->usingOrder($order)
            ->usingSubstatus(Substatus::find(Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED))
            ->create();
        $policy = new OrderPolicy();

        $this->assertFalse($policy->updateName($notOwner, $order));
    }

    /** @test */
    public function it_disallows_to_update_the_name_from_an_order_without_a_valid_substatus()
    {
        $owner = User::factory()->create();
        $order = Order::factory()->usingUser($owner)->create();
        OrderDelivery::factory()->shipmentDelivery()->usingOrder($order)->create();
        OrderSubstatus::factory()
            ->usingOrder($order)
            ->usingSubstatus(Substatus::find(Substatus::STATUS_PENDING_ASSIGNED))
            ->create();
        $policy = new OrderPolicy();

        $this->assertFalse($policy->updateName($owner, $order));
    }
}

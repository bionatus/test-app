<?php

namespace Tests\Unit\Policies\Api\V4\Order;

use App\Models\Order;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Models\User;
use App\Policies\Api\V4\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApproveTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_the_owner_to_approve_a_pending_approval_order()
    {
        $owner    = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order1    = Order::factory()->pendingApproval()->usingUser($owner)->usingSupplier($supplier)->create();
        $order1->substatuses()
            ->withTimestamps()
            ->attach(Substatus::STATUS_PENDING_APPROVAL_FULFILLED);
        $order2    = Order::factory()->pendingApproval()->usingUser($owner)->usingSupplier($supplier)->create();
        $order2->substatuses()
            ->withTimestamps()
            ->attach(Substatus::STATUS_PENDING_APPROVAL_QUOTE_UPDATED);

        $policy = new OrderPolicy();

        $this->assertTrue($policy->approve($owner, $order1));
        $this->assertTrue($policy->approve($owner, $order2));
    }

    /** @test */
    public function it_disallows_another_user_to_approve_an_order()
    {
        $notOwner = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->pendingApproval()->usingSupplier($supplier)->create();
        $order->substatuses()
            ->withTimestamps()
            ->attach(Substatus::STATUS_PENDING_APPROVAL_FULFILLED);

        $policy = new OrderPolicy();

        $this->assertFalse($policy->approve($notOwner, $order));
    }

    /** @test */
    public function it_disallows_the_owner_to_approve_a_non_pending_approval_order()
    {
        $owner    = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->pending()->usingUser($owner)->usingSupplier($supplier)->create();
        $order->substatuses()
            ->withTimestamps()
            ->attach(Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED);

        $policy = new OrderPolicy();

        $this->assertFalse($policy->approve($owner, $order));
    }
}

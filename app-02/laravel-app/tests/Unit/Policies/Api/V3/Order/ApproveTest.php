<?php

namespace Tests\Unit\Policies\Api\V3\Order;

use App\Models\Order;
use App\Models\Supplier;
use App\Models\User;
use App\Policies\Api\V3\OrderPolicy;
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
        $order    = Order::factory()->pendingApproval()->usingUser($owner)->usingSupplier($supplier)->create();

        $policy = new OrderPolicy();

        $this->assertTrue($policy->approve($owner, $order));
    }

    /** @test */
    public function it_disallows_another_user_to_approve_an_order()
    {
        $notOwner = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();

        $policy = new OrderPolicy();

        $this->assertFalse($policy->approve($notOwner, $order));
    }

    /** @test */
    public function it_disallows_the_owner_to_approve_a_non_pending_approval_order()
    {
        $owner    = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->pending()->usingUser($owner)->usingSupplier($supplier)->create();

        $policy = new OrderPolicy();

        $this->assertFalse($policy->approve($owner, $order));
    }
}

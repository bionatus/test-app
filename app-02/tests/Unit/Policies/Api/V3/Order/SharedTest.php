<?php

namespace Tests\Unit\Policies\Api\V3\Order;

use App\Models\Order;
use App\Models\Supplier;
use App\Models\User;
use App\Policies\Api\V3\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SharedTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_the_owner_to_share_an_order()
    {
        $owner    = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingUser($owner)->usingSupplier($supplier)->create();

        $policy = new OrderPolicy();

        $this->assertTrue($policy->share($owner, $order));
    }

    /** @test */
    public function it_disallows_another_user_to_share_an_order()
    {
        $notOwner = User::factory()->create();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();

        $policy = new OrderPolicy();

        $this->assertFalse($policy->share($notOwner, $order));
    }
}

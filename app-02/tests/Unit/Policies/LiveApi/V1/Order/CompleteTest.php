<?php

namespace Tests\Unit\Policies\LiveApi\V1\Order;

use App\Models\Order;
use App\Models\Staff;
use App\Models\Supplier;
use App\Policies\LiveApi\V1\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompleteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_the_owner_to_update_an_order_assigned_and_approved()
    {
        $owner = Staff::factory()->createQuietly();
        $order = Order::factory()->approved()->usingSupplier($owner->supplier)->create([
            'working_on_it' => 'John Doe',
        ]);

        $policy = new OrderPolicy();

        $this->assertTrue($policy->complete($owner, $order));
    }

    /** @test */
    public function it_disallows_another_user_to_update_an_order_assigned_and_approved()
    {
        $notOwner = Staff::factory()->createQuietly();
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->approved()->create([
            'working_on_it' => 'John Doe',
        ]);

        $policy = new OrderPolicy();

        $this->assertFalse($policy->complete($notOwner, $order));
    }

    /** @test */
    public function it_disallows_the_owner_to_update_an_order_not_assigned_and_approved()
    {
        $owner = Staff::factory()->createQuietly();
        $order = Order::factory()->approved()->usingSupplier($owner->supplier)->create();

        $policy = new OrderPolicy();

        $this->assertFalse($policy->complete($owner, $order));
    }

    /** @test */
    public function it_disallows_the_owner_to_update_an_order_assigned_and_not_approved()
    {
        $owner = Staff::factory()->createQuietly();
        $order = Order::factory()->pending()->usingSupplier($owner->supplier)->create();

        $policy = new OrderPolicy();

        $this->assertFalse($policy->complete($owner, $order));
    }
}

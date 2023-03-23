<?php

namespace Tests\Unit\Policies\LiveApi\V1\Order;

use App\Models\Order;
use App\Models\Staff;
use App\Policies\LiveApi\V1\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_the_processor_to_assign_a_pending_order()
    {
        $processor = Staff::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($processor->supplier)->pending()->createQuietly();

        $policy = new OrderPolicy();

        $this->assertTrue($policy->assign($processor, $order));
    }

    /** @test */
    public function it_disallows_another_user_to_assign_a_pending_order()
    {
        $anotherProcessor = Staff::factory()->createQuietly();
        $order            = Order::factory()->pending()->createQuietly();

        $policy = new OrderPolicy();

        $this->assertFalse($policy->assign($anotherProcessor, $order));
    }

    /** @test */
    public function it_disallows_the_processor_to_assign_a_non_pending_order()
    {
        $processor = Staff::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($processor->supplier)->approved()->createQuietly();

        $policy = new OrderPolicy();

        $this->assertFalse($policy->assign($processor, $order));
    }
}

<?php

namespace Tests\Unit\Policies\LiveApi\V2\Order;

use App\Models\Order;
use App\Models\Staff;
use App\Policies\LiveApi\V2\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_the_processor_to_read_an_order()
    {
        $processor = Staff::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($processor->supplier)->createQuietly();

        $policy = new OrderPolicy();

        $this->assertTrue($policy->read($processor, $order));
    }

    /** @test */
    public function it_disallows_another_user_to_read_an_order()
    {
        $anotherProcessor = Staff::factory()->createQuietly();
        $order            = Order::factory()->createQuietly();

        $policy = new OrderPolicy();

        $this->assertFalse($policy->read($anotherProcessor, $order));
    }
}

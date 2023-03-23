<?php

namespace Tests\Unit\Policies\LiveApi\V1\Order;

use App\Models\Order;
use App\Models\Staff;
use App\Policies\LiveApi\V1\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_the_processor_to_update_an_order_assigned_and_pending()
    {
        $processor = Staff::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($processor->supplier)->pending()->create([
            'working_on_it' => 'John Doe',
        ]);

        $policy = new OrderPolicy();

        $this->assertTrue($policy->update($processor, $order));
    }

    /** @test */
    public function it_disallows_another_user_to_update_an_order_assigned_and_pending()
    {
        $anotherProcessor = Staff::factory()->createQuietly();
        $order            = Order::factory()->pending()->createQuietly([
            'working_on_it' => 'John Doe',
        ]);

        $policy = new OrderPolicy();

        $this->assertFalse($policy->update($anotherProcessor, $order));
    }

    /** @test */
    public function it_disallows_the_processor_to_update_an_order_not_assigned_and_pending()
    {
        $processor = Staff::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($processor->supplier)->pending()->create();

        $policy = new OrderPolicy();

        $this->assertFalse($policy->update($processor, $order));
    }

    /** @test */
    public function it_disallows_the_processor_to_update_an_order_assigned_and_not_pending()
    {
        $processor = Staff::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($processor->supplier)->approved()->create([
            'working_on_it' => 'John Doe',
        ]);

        $policy = new OrderPolicy();

        $this->assertFalse($policy->update($processor, $order));
    }
}

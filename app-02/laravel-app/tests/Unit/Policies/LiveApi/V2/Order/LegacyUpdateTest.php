<?php

namespace Tests\Unit\Policies\LiveApi\V2\Order;

use App\Models\Order;
use App\Models\Staff;
use App\Models\Supplier;
use App\Policies\LiveApi\V2\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegacyUpdateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_disallows_another_user_to_update_an_order()
    {
        $anotherProcessor = Staff::factory()->createQuietly();
        $supplier         = Supplier::factory()->createQuietly();
        $order            = Order::factory()->usingSupplier($supplier)->pending()->create();

        $policy = new OrderPolicy();

        $this->assertFalse($policy->legacyUpdate($anotherProcessor, $order));
    }

    /** @test */
    public function it_disallows_the_processor_to_update_an_order_not_pending()
    {
        $processor = Staff::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($processor->supplier)->approved()->create();

        $policy = new OrderPolicy();

        $this->assertFalse($policy->legacyUpdate($processor, $order));
    }

    /** @test */
    public function it_allows_the_processor_to_update_a_pending_order()
    {
        $processor = Staff::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($processor->supplier)->pending()->create();

        $policy = new OrderPolicy();

        $this->assertTrue($policy->legacyUpdate($processor, $order));
    }
}

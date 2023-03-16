<?php

namespace Tests\Unit\Policies\LiveApi\V1\Order;

use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Staff;
use App\Policies\LiveApi\V1\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreApproveTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_the_processor_to_send_an_order_to_approve()
    {
        $processor = Staff::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($processor->supplier)->pending()->create([
            'working_on_it' => 'John Doe',
        ]);
        OrderDelivery::factory()->usingOrder($order)->create();
        ItemOrder::factory()->usingOrder($order)->count(3)->create(['status' => ItemOrder::STATUS_AVAILABLE]);

        $policy = new OrderPolicy();

        $this->assertTrue($policy->preApprove($processor, $order));
    }

    /** @test
     * @dataProvider dataProvider
     */
    public function it_disallows_the_processor_to_pre_approve_an_order_via_curri_delivery_without_fee($fee)
    {
        $processor = Staff::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($processor->supplier)->pending()->create([
            'working_on_it' => 'John Doe',
        ]);
        OrderDelivery::factory()->usingOrder($order)->createQuietly([
            'type' => OrderDelivery::TYPE_CURRI_DELIVERY,
            'fee'  => $fee,
        ]);
        ItemOrder::factory()->usingOrder($order)->count(3)->create(['status' => ItemOrder::STATUS_AVAILABLE]);

        $policy = new OrderPolicy();

        $this->assertFalse($policy->preApprove($processor, $order));
    }

    public function dataProvider(): array
    {
        return [
            [0],
            [null],
        ];
    }

    /** @test */
    public function it_disallows_another_user_to_send_an_order_to_approve()
    {
        $anotherProcessor = Staff::factory()->createQuietly();
        $order            = Order::factory()->pending()->createQuietly([
            'working_on_it' => 'John Doe',
        ]);
        OrderDelivery::factory()->usingOrder($order)->create();
        ItemOrder::factory()->usingOrder($order)->count(3)->create(['status' => ItemOrder::STATUS_AVAILABLE]);

        $policy = new OrderPolicy();

        $this->assertFalse($policy->preApprove($anotherProcessor, $order));
    }

    /** @test */
    public function it_disallows_the_processor_to_send_to_approve_an_order_with_pending_items()
    {
        $processor = Staff::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($processor->supplier)->pending()->create([
            'working_on_it' => 'John Doe',
        ]);
        OrderDelivery::factory()->usingOrder($order)->create();
        ItemOrder::factory()->usingOrder($order)->create(['status' => ItemOrder::STATUS_AVAILABLE]);
        ItemOrder::factory()->usingOrder($order)->create(['status' => ItemOrder::STATUS_PENDING]);

        $policy = new OrderPolicy();

        $this->assertFalse($policy->preApprove($processor, $order));
    }

    /** @test */
    public function it_disallows_the_processor_to_send_to_approve_an_order_without_assignment()
    {
        $processor = Staff::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($processor->supplier)->pending()->create();
        OrderDelivery::factory()->usingOrder($order)->create();
        ItemOrder::factory()->usingOrder($order)->count(3)->create(['status' => ItemOrder::STATUS_AVAILABLE]);

        $policy = new OrderPolicy();

        $this->assertFalse($policy->preApprove($processor, $order));
    }

    /** @test */
    public function it_disallows_the_processor_to_send_to_approve_an_order_with_status_not_pending()
    {
        $processor = Staff::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($processor->supplier)->approved()->create([
            'working_on_it' => 'John Doe',
        ]);
        OrderDelivery::factory()->usingOrder($order)->create();
        ItemOrder::factory()->usingOrder($order)->count(3)->create(['status' => ItemOrder::STATUS_AVAILABLE]);

        $policy = new OrderPolicy();

        $this->assertFalse($policy->preApprove($processor, $order));
    }
}

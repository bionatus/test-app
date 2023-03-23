<?php

namespace Tests\Unit\Policies\LiveApi\V1\Order;

use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Staff;
use App\Policies\LiveApi\V1\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SendForApprovalTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_the_processor_to_send_an_order_to_approve()
    {
        $processor = Staff::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($processor->supplier)->pending()->createQuietly([
            'working_on_it' => 'John Doe',
        ]);
        OrderDelivery::factory()->usingOrder($order)->create([
            'date'       => Carbon::now(),
            'start_time' => Carbon::createFromTime(9)->format('H:i'),
            'end_time'   => Carbon::createFromTime(12)->format('H:i'),
        ]);
        ItemOrder::factory()->usingOrder($order)->count(3)->create(['status' => ItemOrder::STATUS_AVAILABLE]);

        $policy = new OrderPolicy();

        $this->assertTrue($policy->sendForApproval($processor, $order));
    }

    /** @test */
    public function it_disallows_another_user_to_send_an_order_to_approve()
    {
        $anotherProcessor = Staff::factory()->createQuietly();
        $order            = Order::factory()->pending()->createQuietly([
            'working_on_it' => 'John Doe',
        ]);
        OrderDelivery::factory()->usingOrder($order)->create([
            'date'       => Carbon::now(),
            'start_time' => Carbon::createFromTime(9)->format('H:i'),
            'end_time'   => Carbon::createFromTime(12)->format('H:i'),
        ]);
        ItemOrder::factory()->usingOrder($order)->count(3)->create(['status' => ItemOrder::STATUS_AVAILABLE]);

        $policy = new OrderPolicy();

        $this->assertFalse($policy->sendForApproval($anotherProcessor, $order));
    }

    /** @test */
    public function it_disallows_the_processor_to_send_to_approve_an_order_with_pending_items()
    {
        $processor = Staff::factory()->createQuietly();
        $order     = Order::factory()->pending()->usingSupplier($processor->supplier)->create([
            'working_on_it' => 'John Doe',
        ]);
        OrderDelivery::factory()->usingOrder($order)->create([
            'date'       => Carbon::now(),
            'start_time' => Carbon::createFromTime(9)->format('H:i'),
            'end_time'   => Carbon::createFromTime(12)->format('H:i'),
        ]);
        ItemOrder::factory()->usingOrder($order)->create(['status' => ItemOrder::STATUS_AVAILABLE]);
        ItemOrder::factory()->usingOrder($order)->create(['status' => ItemOrder::STATUS_PENDING]);

        $policy = new OrderPolicy();

        $this->assertFalse($policy->sendForApproval($processor, $order));
    }

    /** @test */
    public function it_disallows_the_processor_to_send_to_approve_an_order_without_assignment()
    {
        $processor = Staff::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($processor->supplier)->pending()->create();
        OrderDelivery::factory()->usingOrder($order)->create([
            'date'       => Carbon::now(),
            'start_time' => Carbon::createFromTime(9)->format('H:i'),
            'end_time'   => Carbon::createFromTime(12)->format('H:i'),
        ]);
        ItemOrder::factory()->usingOrder($order)->count(3)->create(['status' => ItemOrder::STATUS_AVAILABLE]);

        $policy = new OrderPolicy();

        $this->assertFalse($policy->sendForApproval($processor, $order));
    }

    /** @test */
    public function it_disallows_the_processor_to_send_to_approve_an_order_with_status_not_pending()
    {
        $processor = Staff::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($processor->supplier)->approved()->create([
            'working_on_it' => 'John Doe',
        ]);
        OrderDelivery::factory()->usingOrder($order)->create([
            'date'       => Carbon::now(),
            'start_time' => Carbon::createFromTime(9)->format('H:i'),
            'end_time'   => Carbon::createFromTime(12)->format('H:i'),
        ]);
        ItemOrder::factory()->usingOrder($order)->count(3)->create(['status' => ItemOrder::STATUS_AVAILABLE]);

        $policy = new OrderPolicy();

        $this->assertFalse($policy->sendForApproval($processor, $order));
    }

    /** @test */
    public function it_disallows_the_processor_to_send_to_approve_an_order_without_availability()
    {
        $processor = Staff::factory()->createQuietly();
        $order     = Order::factory()->usingSupplier($processor->supplier)->pending()->create([
            'working_on_it' => 'John Doe',
        ]);
        OrderDelivery::factory()->usingOrder($order)->create();
        ItemOrder::factory()->usingOrder($order)->count(3)->create(['status' => ItemOrder::STATUS_AVAILABLE]);

        $policy = new OrderPolicy();

        $this->assertFalse($policy->sendForApproval($processor, $order));
    }

    /** @test
     * @dataProvider dataProvider
     */
    public function it_disallows_the_processor_to_send_for_approval_an_order_via_curri_delivery_without_fee($fee)
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

        $this->assertFalse($policy->sendForApproval($processor, $order));
    }

    public function dataProvider(): array
    {
        return [
            [0],
            [null],
        ];
    }
}

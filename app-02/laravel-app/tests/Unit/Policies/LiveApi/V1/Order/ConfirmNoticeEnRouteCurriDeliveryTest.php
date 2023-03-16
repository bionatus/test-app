<?php

namespace Tests\Unit\Policies\LiveApi\V1\Order;

use App\Models\CurriDelivery;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderSubstatus;
use App\Models\Staff;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Policies\LiveApi\V1\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConfirmNoticeEnRouteCurriDeliveryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_the_processor_to_confirm_a_notice_en_route_curri_delivery()
    {
        $supplier      = Supplier::factory()->createQuietly();
        $processor     = Staff::factory()->usingSupplier($supplier)->create();
        $order         = Order::factory()->usingSupplier($supplier)->completed()->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create(['book_id' => 'abc_123']);

        $policy = new OrderPolicy();

        $this->assertTrue($policy->confirmNoticeEnRouteCurriDelivery($processor, $order));
    }

    /** @test */
    public function it_disallows_the_processor_to_confirm_a_notice_en_route_curri_delivery_from_another_supplier()
    {
        $notProcessor  = Staff::factory()->usingSupplier(Supplier::factory()->createQuietly())->create();
        $order         = Order::factory()->approved()->usingSupplier(Supplier::factory()->createQuietly())->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create(['book_id' => 'abc_123']);

        $policy = new OrderPolicy();

        $this->assertFalse($policy->confirmNoticeEnRouteCurriDelivery($notProcessor, $order));
    }

    /**
     * @test
     * @dataProvider orderDataProvider
     */
    public function it_allows_the_processor_to_confirm_a_notice_en_route_curri_delivery_only_when_the_order_is_completed(
        int $substatusId,
        bool $expectedResult
    ) {
        $supplier  = Supplier::factory()->createQuietly();
        $processor = Staff::factory()->usingSupplier($supplier)->create();
        $order     = Order::factory()->usingSupplier($supplier)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create(['book_id' => 'abc_123']);

        $policy = new OrderPolicy();

        $this->assertEquals($expectedResult, $policy->confirmNoticeEnRouteCurriDelivery($processor, $order));
    }

    public function orderDataProvider(): array
    {
        return [
            [Substatus::STATUS_PENDING_REQUESTED, false],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, false],
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY, false],
            [Substatus::STATUS_COMPLETED_DONE, true],
            [Substatus::STATUS_CANCELED_REJECTED, false],
        ];
    }

    /**
     * @test
     * @dataProvider orderDeliveryDataProvider
     */
    public function it_disallows_the_processor_to_confirm_a_non_curri_delivery_order(
        string $orderDeliveryType,
        bool $expectedResult
    ) {
        $supplier      = Supplier::factory()->createQuietly();
        $processor     = Staff::factory()->usingSupplier($supplier)->create();
        $order         = Order::factory()->usingSupplier($supplier)->completed()->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->create(['type' => $orderDeliveryType]);

        if ($orderDeliveryType === OrderDelivery::TYPE_CURRI_DELIVERY) {
            CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create(['book_id' => 'abc_123']);
        }

        $policy = new OrderPolicy();

        $this->assertEquals($expectedResult, $policy->confirmNoticeEnRouteCurriDelivery($processor, $order->refresh()));
    }

    public function orderDeliveryDataProvider(): array
    {
        return [
            [OrderDelivery::TYPE_CURRI_DELIVERY, true],
            [OrderDelivery::TYPE_WAREHOUSE_DELIVERY, false],
            [OrderDelivery::TYPE_PICKUP, false],
            [OrderDelivery::TYPE_SHIPMENT_DELIVERY, false],
        ];
    }

    /** @test */
    public function it_disallows_the_processor_to_confirm_a_notice_en_route_if_the_curri_delivery_is_not_booked()
    {
        $supplier      = Supplier::factory()->createQuietly();
        $processor     = Staff::factory()->usingSupplier($supplier)->create();
        $order         = Order::factory()->usingSupplier($supplier)->approved()->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        $policy = new OrderPolicy();
        $policy->confirmNoticeEnRouteCurriDelivery($processor, $order);

        $this->assertFalse($policy->confirmNoticeEnRouteCurriDelivery($processor, $order));
    }
}

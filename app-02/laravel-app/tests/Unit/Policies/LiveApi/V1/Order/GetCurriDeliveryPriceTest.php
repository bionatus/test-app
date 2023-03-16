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
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetCurriDeliveryPriceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_the_processor_to_get_price_of_a_curri_delivery()
    {
        $supplier      = Supplier::factory()->createQuietly();
        $processor     = Staff::factory()->usingSupplier($supplier)->create();
        $order         = Order::factory()->usingSupplier($supplier)->approved()->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create([
            'date' => Carbon::now()->addDay(),
        ]);
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        $policy = new OrderPolicy();

        $this->assertTrue($policy->getCurriDeliveryPrice($processor, $order));
    }

    /** @test */
    public function it_disallows_the_processor_to_get_price_a_curri_delivery_from_another_supplier()
    {
        $supplier        = Supplier::factory()->createQuietly();
        $anotherSupplier = Supplier::factory()->createQuietly();
        Staff::factory()->usingSupplier($supplier)->create();
        $anotherProcessor = Staff::factory()->usingSupplier($anotherSupplier)->create();
        $order            = Order::factory()->usingSupplier($supplier)->approved()->create();
        $orderDelivery    = OrderDelivery::factory()
            ->usingOrder($order)
            ->curriDelivery()
            ->create(['date' => Carbon::now()->addDay()]);
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        $policy = new OrderPolicy();

        $this->assertFalse($policy->getCurriDeliveryPrice($anotherProcessor, $order));
    }

    /**
     * @test
     * @dataProvider orderDataProvider
     */
    public function it_disallows_the_processor_to_get_price_of_a_curri_delivery_if_the_order_is_not_approved(
        int $substatusId,
        bool $expectedResult
    ) {
        $supplier  = Supplier::factory()->createQuietly();
        $processor = Staff::factory()->usingSupplier($supplier)->create();
        $order     = Order::factory()->usingSupplier($supplier)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create([
            'date' => Carbon::now()->addDay(),
        ]);
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        $policy = new OrderPolicy();

        $this->assertEquals($expectedResult, $policy->getCurriDeliveryPrice($processor, $order));
    }

    public function orderDataProvider(): array
    {
        return [
            [Substatus::STATUS_PENDING_REQUESTED, false],
            [Substatus::STATUS_PENDING_APPROVAL_FULFILLED, false],
            [Substatus::STATUS_APPROVED_AWAITING_DELIVERY, true],
            [Substatus::STATUS_COMPLETED_DONE, false],
            [Substatus::STATUS_CANCELED_REJECTED, false],
        ];
    }

    /**
     * @test
     * @dataProvider orderDeliveryDataProvider
     */
    public function it_disallows_the_processor_to_get_price_a_non_curri_delivery(
        string $orderDeliveryType,
        bool $expectedResult
    ) {
        $supplier      = Supplier::factory()->createQuietly();
        $processor     = Staff::factory()->usingSupplier($supplier)->create();
        $order         = Order::factory()->usingSupplier($supplier)->approved()->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->create([
            'type' => $orderDeliveryType,
            'date' => Carbon::now()->addDay(),
        ]);
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        $policy = new OrderPolicy();

        $this->assertEquals($expectedResult, $policy->getCurriDeliveryPrice($processor, $order->refresh()));
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
    public function it_disallows_the_processor_to_get_price_if_the_curri_is_confirmed_by_supplier()
    {
        $supplier      = Supplier::factory()->createQuietly();
        $processor     = Staff::factory()->usingSupplier($supplier)->create();
        $order         = Order::factory()->usingSupplier($supplier)->approved()->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create([
            'date' => Carbon::now()->addDay(),
        ]);
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->confirmedBySupplier()->create();

        $policy = new OrderPolicy();

        $this->assertFalse($policy->getCurriDeliveryPrice($processor, $order));
    }
}

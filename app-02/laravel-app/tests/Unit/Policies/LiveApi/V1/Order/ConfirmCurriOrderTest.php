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
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ConfirmCurriOrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_the_processor_to_confirm_a_curri_delivery()
    {
        $supplier      = Supplier::factory()->createQuietly();
        $processor     = Staff::factory()->usingSupplier($supplier)->create();
        $order         = Order::factory()->usingSupplier($supplier)->approved()->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create([
            'date'       => Carbon::now()->addDay(),
            'start_time' => Carbon::createFromTime(9)->format('H:i'),
            'end_time'   => Carbon::createFromTime(12)->format('H:i'),
        ]);
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        $policy = new OrderPolicy();

        $this->assertTrue($policy->confirmCurriOrder($processor, $order));
    }

    /** @test */
    public function it_disallows_the_processor_to_confirm_a_curri_delivery_from_another_supplier()
    {
        $notProcessor  = Staff::factory()->usingSupplier(Supplier::factory()->createQuietly())->create();
        $order         = Order::factory()->approved()->usingSupplier(Supplier::factory()->createQuietly())->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create([
            'date'       => Carbon::now()->addDay(),
            'start_time' => Carbon::createFromTime(9)->format('H:i'),
            'end_time'   => Carbon::createFromTime(12)->format('H:i'),
        ]);
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        $policy = new OrderPolicy();

        $this->assertFalse($policy->confirmCurriOrder($notProcessor, $order));
    }

    /**
     * @test
     * @dataProvider orderDataProvider
     */
    public function it_allows_the_processor_to_confirm_a_curri_delivery_only_when_the_order_is_approved(
        int $substatusId,
        bool $expectedResult
    ) {
        $supplier  = Supplier::factory()->createQuietly();
        $processor = Staff::factory()->usingSupplier($supplier)->create();
        $order     = Order::factory()->usingSupplier($supplier)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId($substatusId)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create([
            'date'       => Carbon::now()->addDay(),
            'start_time' => Carbon::createFromTime(9)->format('H:i'),
            'end_time'   => Carbon::createFromTime(12)->format('H:i'),
        ]);
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        $policy = new OrderPolicy();

        $this->assertEquals($expectedResult, $policy->confirmCurriOrder($processor, $order));
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
    public function it_disallows_the_processor_to_confirm_a_non_curri_delivery(
        string $orderDeliveryType,
        bool $expectedResult
    ) {
        $supplier      = Supplier::factory()->createQuietly();
        $processor     = Staff::factory()->usingSupplier($supplier)->create();
        $order         = Order::factory()->usingSupplier($supplier)->approved()->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->create([
            'date'       => Carbon::now()->addDay(),
            'start_time' => Carbon::createFromTime(9)->format('H:i'),
            'end_time'   => Carbon::createFromTime(12)->format('H:i'),
            'type'       => $orderDeliveryType,
        ]);

        if ($orderDeliveryType === OrderDelivery::TYPE_CURRI_DELIVERY) {
            CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();
        }

        $policy = new OrderPolicy();

        $this->assertEquals($expectedResult, $policy->confirmCurriOrder($processor, $order->refresh()));
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
    public function it_disallows_the_processor_to_confirm_a_confirmed_by_supplier_order_delivery()
    {
        $supplier      = Supplier::factory()->createQuietly();
        $processor     = Staff::factory()->usingSupplier($supplier)->create();
        $order         = Order::factory()->usingSupplier($supplier)->approved()->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create([
            'date'       => Carbon::now()->addDay(),
            'start_time' => Carbon::createFromTime(9)->format('H:i'),
            'end_time'   => Carbon::createFromTime(12)->format('H:i'),
        ]);
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->confirmedBySupplier()->create();

        $policy = new OrderPolicy();

        $this->assertFalse($policy->confirmCurriOrder($processor, $order));
    }

    /**
     * @test
     * @dataProvider dateProvider
     */
    public function it_disallows_the_processor_to_confirm_a_curri_delivery_expired(string $now, bool $expected)
    {
        Carbon::setTestNow($now);

        $supplier      = Supplier::factory()->createQuietly();
        $processor     = Staff::factory()->usingSupplier($supplier)->create();
        $order         = Order::factory()->usingSupplier($supplier)->approved()->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create([
            'date'       => '2022-12-08',
            'start_time' => Carbon::createFromTime(9)->format('H:i'),
            'end_time'   => Carbon::createFromTime(12)->format('H:i'),
        ]);
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        $policy = new OrderPolicy();
        $policy->confirmCurriOrder($processor, $order);

        $this->assertEquals($expected, $policy->confirmCurriOrder($processor, $order));
    }

    public function dateProvider(): array
    {
        return [
            // $now, $expected
            ['2022-12-09 00:00:00', false],
            ['2022-12-08 12:00:00', false],
            ['2022-12-08 11:59:00', true],
            ['2022-12-08 10:00:00', true],
        ];
    }
}

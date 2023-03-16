<?php

namespace Tests\Unit\Models;

use App\Handlers\OrderSubstatus\OrderSubstatusCurriHandler;
use App\Models\CurriDelivery;
use App\Models\IsDeliverable;
use App\Models\IsOrderDelivery;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Supplier;
use Config;
use ReflectionClass;
use ReflectionException;

class CurriDeliveryTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(CurriDelivery::tableName(), [
            'id',
            'origin_address_id',
            'destination_address_id',
            'vehicle_type',
            'quote_id',
            'book_id',
            'supplier_confirmed_at',
            'user_confirmed_at',
            'tracking_id',
            'status',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_implements_is_deliverable_interface()
    {
        $reflection = new ReflectionClass(CurriDelivery::class);

        $this->assertTrue($reflection->implementsInterface(IsDeliverable::class));
    }

    /** @test
     * @throws ReflectionException
     */
    public function it_uses_the_corresponding_traits()
    {
        $this->assertUseTrait(CurriDelivery::class, IsOrderDelivery::class);
    }

    /** @test */
    public function it_knows_if_uses_a_destination_address()
    {
        $this->assertTrue(CurriDelivery::usesDestinationAddress());
    }

    /** @test */
    public function it_knows_if_uses_an_origin_address()
    {
        $this->assertTrue(CurriDelivery::usesOriginAddress());
    }

    /** @test */
    public function it_knows_if_has_a_destination_address()
    {
        $curriDelivery = CurriDelivery::factory()->createQuietly();

        $this->assertTrue($curriDelivery->hasDestinationAddress());
    }

    /** @test */
    public function it_knows_if_has_an_origin_address()
    {
        $curriDelivery = CurriDelivery::factory()->createQuietly();

        $this->assertTrue($curriDelivery->hasOriginAddress());
    }

    /** @test */
    public function it_knows_if_is_confirmed_by_supplier()
    {
        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->usingSupplier($supplier)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();
        $curriDelivery = CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->confirmedBySupplier()->create();

        $anotherOrder         = Order::factory()->usingSupplier($supplier)->create();
        $anotherOrderDelivery = OrderDelivery::factory()->usingOrder($anotherOrder)->curriDelivery()->create();
        $anotherCurriDelivery = CurriDelivery::factory()->usingOrderDelivery($anotherOrderDelivery)->create();

        $this->assertTrue($curriDelivery->isConfirmedBySupplier());
        $this->assertFalse($anotherCurriDelivery->isConfirmedBySupplier());
    }

    /** @test */
    public function it_knows_if_is_confirmed_by_user()
    {
        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->usingSupplier($supplier)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();
        $curriDelivery = CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->confirmedByUser()->create();

        $anotherOrder         = Order::factory()->usingSupplier($supplier)->create();
        $anotherOrderDelivery = OrderDelivery::factory()->usingOrder($anotherOrder)->curriDelivery()->create();
        $anotherCurriDelivery = CurriDelivery::factory()->usingOrderDelivery($anotherOrderDelivery)->create();

        $this->assertTrue($curriDelivery->isConfirmedByUser());
        $this->assertFalse($anotherCurriDelivery->isConfirmedByUser());
    }

    /** @test */
    public function it_has_tracking_url_attribute()
    {
        Config::set('curri.prefix_tracking_url', $trackingUrlPrefix = 'url-prefix/');

        $supplier      = Supplier::factory()->createQuietly();
        $order         = Order::factory()->usingSupplier($supplier)->create();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();
        $curriDelivery = CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        $anotherOrder         = Order::factory()->usingSupplier($supplier)->create();
        $anotherOrderDelivery = OrderDelivery::factory()->usingOrder($anotherOrder)->curriDelivery()->create();
        $anotherCurriDelivery = CurriDelivery::factory()->usingOrderDelivery($anotherOrderDelivery)->create([
            'tracking_id' => $trackingId = '1234567890',
        ]);

        $trackingUrl = $trackingUrlPrefix . $trackingId;

        $this->assertNull($curriDelivery->tracking_url);
        $this->assertSame($trackingUrl, $anotherCurriDelivery->tracking_url);
    }

    /** @test */
    public function it_knows_if_is_booked()
    {
        $supplier            = Supplier::factory()->createQuietly();
        $order               = Order::factory()->usingSupplier($supplier)->create();
        $orderDelivery       = OrderDelivery::factory()->usingOrder($order)->create();
        $curriDeliveryBooked = CurriDelivery::factory()
            ->usingOrderDelivery($orderDelivery)
            ->create(['book_id' => 'fake-book-id']);

        $anotherOrder           = Order::factory()->usingSupplier($supplier)->create();
        $anotherOrderDelivery   = OrderDelivery::factory()->usingOrder($anotherOrder)->create();
        $curriDeliveryNotBooked = CurriDelivery::factory()->usingOrderDelivery($anotherOrderDelivery)->create();

        $this->assertFalse($curriDeliveryNotBooked->isBooked());
        $this->assertTrue($curriDeliveryBooked->isBooked());
    }

    /** @test */
    public function it_creates_an_order_substatus_curri_handler()
    {
        $order         = Order::factory()->pending()->createQuietly();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();
        $delivery      = CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        $handler      = $delivery->createSubstatusHandler($order);
        $currentClass = new ReflectionClass($handler);
        $this->assertSame(OrderSubstatusCurriHandler::class, $currentClass->name);
    }
}

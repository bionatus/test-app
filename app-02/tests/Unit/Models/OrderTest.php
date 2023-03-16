<?php

namespace Tests\Unit\Models;

use App\Constants\MediaCollectionNames;
use App\Handlers\OrderSubstatus\OrderSubstatusCurriHandler;
use App\Handlers\OrderSubstatus\OrderSubstatusPickupHandler;
use App\Handlers\OrderSubstatus\OrderSubstatusShipmentHandler;
use App\Models\CurriDelivery;
use App\Models\Item;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Pickup;
use App\Models\Point;
use App\Models\Staff;
use App\Models\Status;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;
use Spatie\MediaLibrary\InteractsWithMedia;

class OrderTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Order::tableName(), [
            'id',
            'uuid',
            'user_id',
            'supplier_id',
            'name',
            'working_on_it',
            'oem_id',
            'bid_number',
            'total',
            'paid_total',
            'discount',
            'total',
            'paid_total',
            'tax',
            'note',
            'company_id',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_uses_uuid()
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create(['uuid' => Str::uuid()->toString()]);

        $this->assertEquals($order->uuid, $order->getRouteKey());
    }

    /** @test */
    public function it_fills_uuid_on_creation()
    {
        $order = Order::factory()->make(['uuid' => null]);
        $order->save();

        $this->assertNotNull($order->uuid);
    }

    /** @test
     * @throws ReflectionException
     */
    public function it_uses_interacts_with_media_trait()
    {
        $this->assertUseTrait(Order::class, InteractsWithMedia::class, ['registerMediaCollections']);
    }

    /** @test */
    public function it_registers_media_collections()
    {
        $supportCallCategory = Order::factory()->create();
        $supportCallCategory->registerMediaCollections();

        $mediaCollectionNames = Collection::make($supportCallCategory->mediaCollections)->pluck('name');
        $this->assertContains(MediaCollectionNames::INVOICE, $mediaCollectionNames);
    }

    /** @test */
    public function it_knows_if_a_user_is_its_owner()
    {
        $notOwner = User::factory()->create();
        $owner    = User::factory()->create();

        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->usingUser($owner)->create();

        $this->assertFalse($order->isOwner($notOwner));
        $this->assertTrue($order->isOwner($owner));
    }

    /** @test */
    public function it_knows_if_a_staff_is_its_processor()
    {
        $supplierProcessor        = Supplier::factory()->createQuietly();
        $processor                = Staff::factory()->usingSupplier($supplierProcessor)->create();
        $supplierAnotherProcessor = Supplier::factory()->createQuietly();
        $anotherProcessor         = Staff::factory()->usingSupplier($supplierAnotherProcessor)->create();

        $order = Order::factory()->usingSupplier($supplierProcessor)->create();

        $this->assertFalse($order->isProcessor($anotherProcessor));
        $this->assertTrue($order->isProcessor($processor));
    }

    /** @test */
    public function it_knows_if_is_assigned()
    {
        $supplier         = Supplier::factory()->createQuietly();
        $orderNotAssigned = Order::factory()->usingSupplier($supplier)->create();
        $orderAssigned    = Order::factory()->usingSupplier($supplier)->create(['working_on_it' => 'John Doe']);

        $this->assertFalse($orderNotAssigned->isAssigned());
        $this->assertTrue($orderAssigned->isAssigned());
    }

    /** @test */
    public function it_knows_if_is_pending()
    {
        $supplier        = Supplier::factory()->createQuietly();
        $orderNotPending = Order::factory()->usingSupplier($supplier)->approved()->create();
        $orderPending    = Order::factory()->usingSupplier($supplier)->pending()->create();

        $this->assertFalse($orderNotPending->isPending());
        $this->assertTrue($orderPending->isPending());
    }

    /** @test */
    public function it_knows_if_is_pending_approval()
    {
        $supplier = Supplier::factory()->createQuietly();

        $orderNotPendingApproval = Order::factory()->usingSupplier($supplier)->pending()->create();
        $orderPendingApproval    = Order::factory()->usingSupplier($supplier)->pendingApproval()->create();

        $this->assertFalse($orderNotPendingApproval->isPendingApproval());
        $this->assertTrue($orderPendingApproval->isPendingApproval());
    }

    /** @test */
    public function it_knows_if_is_approved()
    {
        $supplier         = Supplier::factory()->createQuietly();
        $orderNotApproved = Order::factory()->usingSupplier($supplier)->pending()->create();
        $orderApproved    = Order::factory()->usingSupplier($supplier)->approved()->create();

        $this->assertFalse($orderNotApproved->isApproved());
        $this->assertTrue($orderApproved->isApproved());
    }

    /** @test */
    public function it_knows_if_is_canceled()
    {
        $supplier         = Supplier::factory()->createQuietly();
        $orderCanceled    = Order::factory()->usingSupplier($supplier)->canceled()->create();
        $orderNotCanceled = Order::factory()->usingSupplier($supplier)->pending()->create();

        $this->assertTrue($orderCanceled->isCanceled());
        $this->assertFalse($orderNotCanceled->isCanceled());
    }

    /** @test */
    public function it_knows_if_is_completed()
    {
        $supplier          = Supplier::factory()->createQuietly();
        $orderNotCompleted = Order::factory()->usingSupplier($supplier)->pending()->create();
        $orderCompleted    = Order::factory()->usingSupplier($supplier)->completed()->create();

        $this->assertFalse($orderNotCompleted->isCompleted());
        $this->assertTrue($orderCompleted->isCompleted());
    }

    /** @test */
    public function it_knows_if_its_last_substatus_is_any_of_given_values()
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();
        $order->substatuses()->withTimestamps()->attach(Substatus::STATUS_CANCELED_REJECTED);
        $order->substatuses()->withTimestamps()->attach(Substatus::STATUS_APPROVED_READY_FOR_DELIVERY);

        $this->assertTrue($order->lastSubStatusIsAnyOf([
            Substatus::STATUS_COMPLETED_DONE,
            Substatus::STATUS_APPROVED_READY_FOR_DELIVERY,
            Substatus::STATUS_CANCELED_CANCELED,
        ]));
        $this->assertFalse($order->lastSubStatusIsAnyOf([
            Substatus::STATUS_PENDING_REQUESTED,
            Substatus::STATUS_PENDING_APPROVAL_QUOTE_NEEDED,
            Substatus::STATUS_CANCELED_REJECTED,
        ]));
        $this->assertFalse($order->lastSubStatusIsAnyOf([]));
    }

    /** @test */
    public function it_knows_if_has_availability()
    {
        $supplier                 = Supplier::factory()->createQuietly();
        $orderWithoutAvailability = Order::factory()->usingSupplier($supplier)->create();
        OrderDelivery::factory()->usingOrder($orderWithoutAvailability)->create();
        $orderWithAvailability = Order::factory()->usingSupplier($supplier)->create();
        OrderDelivery::factory()->usingOrder($orderWithAvailability)->create([
            'date'       => Carbon::now(),
            'start_time' => Carbon::createFromTime(9)->format('H:i'),
            'end_time'   => Carbon::createFromTime(12)->format('H:i'),
        ]);

        $this->assertFalse($orderWithoutAvailability->hasAvailability());
        $this->assertTrue($orderWithAvailability->hasAvailability());
    }

    /** @test */
    public function it_knows_if_doesnt_have_pending_items()
    {
        $supplier              = Supplier::factory()->createQuietly();
        $orderWithPendingItems = Order::factory()->usingSupplier($supplier)->create();
        ItemOrder::factory()
            ->count(3)
            ->usingOrder($orderWithPendingItems)
            ->create(['status' => ItemOrder::STATUS_AVAILABLE]);
        ItemOrder::factory()
            ->count(3)
            ->usingOrder($orderWithPendingItems)
            ->create(['status' => ItemOrder::STATUS_PENDING]);
        $this->assertFalse($orderWithPendingItems->doesntHavePendingItems());

        $orderWithoutPendingItems = Order::factory()->usingSupplier($supplier)->create();
        ItemOrder::factory()
            ->count(2)
            ->usingOrder($orderWithoutPendingItems)
            ->create(['status' => ItemOrder::STATUS_AVAILABLE]);
        ItemOrder::factory()
            ->count(3)
            ->usingOrder($orderWithoutPendingItems)
            ->create(['status' => ItemOrder::STATUS_NOT_AVAILABLE]);
        ItemOrder::factory()
            ->count(4)
            ->usingOrder($orderWithoutPendingItems)
            ->create(['status' => ItemOrder::STATUS_SEE_BELOW_ITEM]);
        $this->assertTrue($orderWithoutPendingItems->doesntHavePendingItems());
    }

    /** @test */
    public function it_knows_if_had_truck_stocks()
    {
        $supplier = Supplier::factory()->createQuietly();

        $orderWithTruckStockAdded = Order::factory()->usingSupplier($supplier)->create();
        ItemOrder::factory()->usingOrder($orderWithTruckStockAdded)->notInitialRequest()->create();
        $this->assertTrue($orderWithTruckStockAdded->hadTruckStock());

        $orderWithoutTruckStock = Order::factory()->usingSupplier($supplier)->create();
        ItemOrder::factory()->count(2)->usingOrder($orderWithoutTruckStock)->create();
        $this->assertFalse($orderWithoutTruckStock->hadTruckStock());
    }

    /** @test */
    public function it_does_not_consider_removed_or_see_below_supply_items_when_checking_if_it_had_truck_stocks()
    {
        $supplier     = Supplier::factory()->createQuietly();
        $seeBelowItem = Item::factory()->supply()->create();
        $removedItem  = Item::factory()->supply()->create();

        $orderWithoutTruckStock = Order::factory()->usingSupplier($supplier)->create([
            'created_at' => Carbon::now()->subDay(),
        ]);
        ItemOrder::factory()
            ->usingOrder($orderWithoutTruckStock)
            ->usingItem($seeBelowItem)
            ->create(['status' => ItemOrder::STATUS_SEE_BELOW_ITEM]);
        ItemOrder::factory()
            ->usingOrder($orderWithoutTruckStock)
            ->usingItem($removedItem)
            ->create(['status' => ItemOrder::STATUS_REMOVED]);
        $this->assertFalse($orderWithoutTruckStock->hadTruckStock());
    }

    /** @test */
    public function it_knows_its_total()
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();
        ItemOrder::factory()->usingOrder($order)->count(10)->create([
            'status'   => ItemOrder::STATUS_AVAILABLE,
            'price'    => 75,
            'quantity' => 3,
        ]);
        ItemOrder::factory()->usingOrder($order)->count(10)->create([
            'status'   => ItemOrder::STATUS_NOT_AVAILABLE,
            'price'    => 50,
            'quantity' => 1,
        ]);
        ItemOrder::factory()->usingOrder($order)->count(10)->create([
            'status'   => ItemOrder::STATUS_PENDING,
            'price'    => 50,
            'quantity' => 1,
        ]);
        ItemOrder::factory()->usingOrder($order)->count(10)->create([
            'status'   => ItemOrder::STATUS_SEE_BELOW_ITEM,
            'price'    => 50,
            'quantity' => 1,
        ]);

        $this->assertEquals(2250, $order->subTotal());
    }

    /** @test
     * @dataProvider legacyAvailabilityProvider
     */
    public function it_knows_availability_translation(
        Carbon $date = null,
        string $startTime = null,
        string $endTime = null,
        string $expectedTranslation
    ) {

        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();

        OrderDelivery::factory()->usingOrder($order)->create([
            'date'       => $date,
            'start_time' => $startTime,
            'end_time'   => $endTime,
        ]);

        $this->assertSame($expectedTranslation, $order->availabilityTranslation());
    }

    public function legacyAvailabilityProvider(): array
    {
        return [
            [
                Carbon::createFromFormat('Y-m-d', '2022-06-29'),
                Carbon::createFromTime(9)->format('H:i'),
                Carbon::createFromTime(17)->format('H:i'),
                '06-29-2022 9AM - 5PM',
            ],
            [
                null,
                Carbon::createFromTime(9)->format('H:i'),
                Carbon::createFromTime(17)->format('H:i'),
                '',
            ],
            [
                Carbon::createFromFormat('Y-m-d', '2022-06-29'),
                null,
                Carbon::createFromTime(17)->format('H:i'),
                '',
            ],
            [
                Carbon::createFromFormat('Y-m-d', '2022-06-29'),
                Carbon::createFromTime(9)->format('H:i'),
                null,
                '',
            ],
            [null, null, null, ''],
        ];
    }

    /** @test */
    public function it_knows_its_delivery_fee()
    {
        $supplier          = Supplier::factory()->createQuietly();
        $order             = Order::factory()->usingSupplier($supplier)->create();
        $orderDelivery     = OrderDelivery::factory()->usingOrder($order)->create();
        $orderWithDelivery = $orderDelivery->order;

        $orderWithoutDelivery = Order::factory()->usingSupplier($supplier)->create();

        $this->assertSame($orderDelivery->fee, $orderWithDelivery->deliveryFee());
        $this->assertSame(0, $orderWithoutDelivery->deliveryFee());
    }

    /** @test */
    public function it_knows_its_subtotal_with_delivery_and_discount()
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create(['discount' => 10]);
        ItemOrder::factory()->usingOrder($order)->count(3)->available()->create(['price' => 123, 'quantity' => 1]);
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->create(['fee' => 5]);

        $subtotalWithDeliveryAndDiscount = $order->subTotal() + $orderDelivery->fee - $order->discount;

        $this->assertSame($subtotalWithDeliveryAndDiscount, $order->subTotalWithDeliveryAndDiscount());
    }

    /** @test */
    public function it_knows_its_subtotal_with_delivery()
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();
        ItemOrder::factory()->usingOrder($order)->count(3)->available()->create(['price' => 123, 'quantity' => 1]);
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->create(['fee' => 5]);

        $subtotalWithDelivery = $order->subTotal() + $orderDelivery->fee;

        $this->assertSame($subtotalWithDelivery, $order->subTotalWithDelivery());
    }

    /** @test */
    public function it_knows_the_total_points_earned()
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();

        $completed = Point::factory()->usingOrder($order)->count(3)->create();
        $canceled  = Point::factory()->usingOrder($order)->count(2)->orderCanceled()->create();
        Point::factory()->usingOrder($order)->redeemed()->create();
        $expected = $completed->merge($canceled)->sum('points_earned');

        $this->assertSame($expected, $order->totalPointsEarned());
    }

    /** @test */
    public function it_knows_the_status_name()
    {
        $order    = Order::factory()->pending()->create();
        $expected = Status::STATUS_NAME_PENDING;
        $this->assertSame($expected, $order->getStatusName());
    }

    /** @test */
    public function it_will_not_log_activity_on_create()
    {
        $order = Order::factory()->createQuietly();
        $this->assertEquals(0, $order->activities->count());
    }

    /** @test */
    public function it_will_log_activity_on_update()
    {
        $order = Order::factory()->createQuietly();
        $order->update(['name' => 'new name']);

        $this->assertEquals(1, $order->activities->count());
        $this->assertDatabaseHas('activity_log', [
            'log_name'     => 'order_log',
            'description'  => 'order.updated',
            'subject_type' => 'order',
            'subject_id'   => $order->getKey(),
            'resource'     => 'order',
            'event'        => 'updated',
        ]);
    }

    /** @test */
    public function it_creates_an_order_substatus_curri_handler_when_delivery_is_curri()
    {
        $order         = Order::factory()->pending()->createQuietly();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->curriDelivery()->create();
        CurriDelivery::factory()->usingOrderDelivery($orderDelivery)->create();

        $handler      = $order->createSubstatusCurriHandler();
        $currentClass = new ReflectionClass($handler);
        $this->assertSame(OrderSubstatusCurriHandler::class, $currentClass->name);
    }

    /** @test */
    public function it_creates_an_order_substatus_pickup_handler_when_delivery_is_pickup()
    {
        $order         = Order::factory()->pending()->createQuietly();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->pickup()->create();
        Pickup::factory()->usingOrderDelivery($orderDelivery)->create();

        $handler      = $order->createSubstatusPickupHandler();
        $currentClass = new ReflectionClass($handler);
        $this->assertSame(OrderSubstatusPickupHandler::class, $currentClass->name);
    }

    /** @test */
    public function it_creates_an_order_substatus_shipment_handler_when_delivery_is_shipment()
    {
        $order         = Order::factory()->pending()->createQuietly();
        $orderDelivery = OrderDelivery::factory()->usingOrder($order)->shipmentDelivery()->create();
        Pickup::factory()->usingOrderDelivery($orderDelivery)->create();

        $handler      = $order->createSubstatusShipmentHandler();
        $currentClass = new ReflectionClass($handler);
        $this->assertSame(OrderSubstatusShipmentHandler::class, $currentClass->name);
    }
}

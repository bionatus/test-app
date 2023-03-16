<?php

namespace Tests\Unit\Models\Order;

use App\Models\CartOrder;
use App\Models\Company;
use App\Models\Item;
use App\Models\ItemOrder;
use App\Models\MissedOrderRequest;
use App\Models\Oem;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderInvoice;
use App\Models\OrderLockedData;
use App\Models\OrderSnap;
use App\Models\OrderStaff;
use App\Models\OrderSubstatus;
use App\Models\PendingApprovalView;
use App\Models\Point;
use App\Models\SharedOrder;
use App\Models\Staff;
use App\Models\Substatus;
use App\Models\Supplier;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Order $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Order::factory()->createQuietly();
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $related = $this->instance->user()->first();

        $this->assertInstanceOf(User::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_supplier()
    {
        $related = $this->instance->supplier()->first();

        $this->assertInstanceOf(Supplier::class, $related);
    }

    /** @test */
    public function it_has_staffs()
    {
        OrderStaff::factory()->usingOrder($this->instance)->count(10)->createQuietly();

        $related = $this->instance->staffs()->get();

        $this->assertCorrectRelation($related, Staff::class);
    }

    /** @test */
    public function it_has_order_staffs()
    {
        OrderStaff::factory()->usingOrder($this->instance)->count(10)->createQuietly();

        $related = $this->instance->orderStaffs()->get();

        $this->assertCorrectRelation($related, OrderStaff::class);
    }

    /** @test */
    public function it_has_a_last_staff()
    {
        OrderStaff::factory()->usingOrder($this->instance)->createQuietly();

        $related = $this->instance->lastOrderStaff;

        $this->assertInstanceOf(OrderStaff::class, $related);
    }

    /** @test */
    public function its_last_staff_returns_the_last_staff()
    {
        $staff = Staff::factory()->createQuietly();
        $order = Order::factory()->create();
        OrderStaff::factory()->usingOrder($order)->usingStaff($staff)->create();
        $staffTwo       = Staff::factory()->createQuietly();
        $lastOrderstaff = OrderStaff::factory()->usingOrder($order)->usingStaff($staffTwo)->create();

        $this->assertSame($lastOrderstaff->id, $order->lastOrderStaff->id);
    }

    /** @test */
    public function it_has_missed_order_requests()
    {
        $supplier = Supplier::factory()->createQuietly();
        MissedOrderRequest::factory()->usingOrder($this->instance)->usingSupplier($supplier)->count(10)->create();

        $related = $this->instance->missedOrderRequests()->get();

        $this->assertCorrectRelation($related, MissedOrderRequest::class);
    }

    /** @test */
    public function it_has_items()
    {
        ItemOrder::factory()->usingOrder($this->instance)->count(10)->create();

        $related = $this->instance->items()->get();

        $this->assertCorrectRelation($related, Item::class);
    }

    /** @test */
    public function it_has_item_orders()
    {
        ItemOrder::factory()->usingOrder($this->instance)->count(10)->create();

        $related = $this->instance->itemOrders()->get();

        $this->assertCorrectRelation($related, ItemOrder::class);
    }

    /** @test */
    public function it_has_active_item_orders()
    {
        ItemOrder::factory()->usingOrder($this->instance)->available()->count(5)->create();
        ItemOrder::factory()->usingOrder($this->instance)->pending()->count(5)->create();

        $related = $this->instance->activeItemOrders()->get();

        $this->assertCorrectRelation($related, ItemOrder::class);
    }

    /** @test */
    public function it_has_available_item_orders()
    {
        ItemOrder::factory()->usingOrder($this->instance)->available()->count(10)->create();

        $related = $this->instance->activeItemOrders()->get();

        $this->assertCorrectRelation($related, ItemOrder::class);
    }

    /** @test */
    public function it_has_available_and_removed_item_orders()
    {
        ItemOrder::factory()->usingOrder($this->instance)->available()->count(5)->create();
        ItemOrder::factory()->usingOrder($this->instance)->removed()->count(5)->create();

        $related = $this->instance->availableAndRemovedItemOrders()->get();

        $this->assertCorrectRelation($related, ItemOrder::class);
    }

    /** @test */
    public function it_belongs_to_an_oem()
    {
        $this->instance->oem()->associate(Oem::factory()->create());

        $related = $this->instance->oem()->first();

        $this->assertInstanceOf(Oem::class, $related);
    }

    /** @test */
    public function it_has_order_delivery()
    {
        OrderDelivery::factory()->usingOrder($this->instance)->create();

        $related = $this->instance->orderDelivery()->first();

        $this->assertInstanceOf(OrderDelivery::class, $related);
    }

    /** @test */
    public function it_has_order_locked_data()
    {
        OrderLockedData::factory()->usingOrder($this->instance)->create();

        $related = $this->instance->orderLockedData()->first();

        $this->assertInstanceOf(OrderLockedData::class, $related);
    }

    /** @test */
    public function it_has_points()
    {
        Point::factory()->usingOrder($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->points()->get();

        $this->assertCorrectRelation($related, Point::class);
    }

    /** @test */
    public function it_has_order_invoices()
    {
        OrderInvoice::factory()->usingOrder($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->orderInvoices;

        $this->assertCorrectRelation($related, OrderInvoice::class);
    }

    /** @test */
    public function it_has_an_invoice()
    {
        OrderInvoice::factory()->invoice()->usingOrder($this->instance)->create();

        $related = $this->instance->invoice;

        $this->assertInstanceOf(OrderInvoice::class, $related);
    }

    /** @test */
    public function its_invoice_is_an_order_invoice_with_type_invoice()
    {
        $invoice = OrderInvoice::factory()->invoice()->usingOrder($this->instance)->create();
        OrderInvoice::factory()->credit()->usingOrder($this->instance)->create();

        $related = $this->instance->invoice;

        $this->assertSame($invoice->type, $related->type);
    }

    /** @test */
    public function it_has_a_credit()
    {
        OrderInvoice::factory()->credit()->usingOrder($this->instance)->create();

        $related = $this->instance->credit;

        $this->assertInstanceOf(OrderInvoice::class, $related);
    }

    /** @test */
    public function its_credit_is_an_order_invoice_with_type_credit()
    {
        $credit = OrderInvoice::factory()->credit()->usingOrder($this->instance)->create();
        OrderInvoice::factory()->invoice()->usingOrder($this->instance)->create();

        $related = $this->instance->credit;

        $this->assertSame($credit->type, $related->type);
    }

    /** @test */
    public function it_has_order_substatuses()
    {
        OrderSubstatus::factory()->usingOrder($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->orderSubstatuses()->get();

        $this->assertCorrectRelation($related, OrderSubstatus::class);
    }

    /** @test */
    public function it_has_substatuses()
    {
        OrderSubstatus::factory()->usingOrder($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->substatuses()->get();

        $this->assertCorrectRelation($related, Substatus::class);
    }

    /** @test */
    public function it_has_a_last_status()
    {
        OrderSubstatus::factory()->usingOrder($this->instance)->create();

        $related = $this->instance->lastStatus;

        $this->assertInstanceOf(OrderSubstatus::class, $related);
    }

    /** @test */
    public function its_last_status_returns_the_last_status()
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->pending()->usingSupplier($supplier)->create();
        OrderSubstatus::factory()->usingOrder($order)->usingSubstatusId(Substatus::STATUS_COMPLETED_DONE)->create();
        $lastStatus = OrderSubstatus::factory()
            ->usingOrder($order)
            ->usingSubstatusId(Substatus::STATUS_APPROVED_AWAITING_DELIVERY)
            ->create();

        $this->assertSame($lastStatus->getKey(), $order->lastStatus->getKey());
    }

    /** @test */
    public function it_has_shared_orders()
    {
        SharedOrder::factory()->usingOrder($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->sharedOrders()->get();

        $this->assertCorrectRelation($related, SharedOrder::class);
    }

    /** @test */
    public function it_has_pending_approval_view()
    {
        PendingApprovalView::factory()->usingOrder($this->instance)->create();

        $related = $this->instance->pendingApprovalView()->first();

        $this->assertInstanceOf(PendingApprovalView::class, $related);
    }

    /** @test */
    public function it_has_a_cart_order()
    {
        CartOrder::factory()->usingOrder($this->instance)->create();

        $related = $this->instance->cartOrder;

        $this->assertInstanceOf(CartOrder::class, $related);
    }

    /** @test */
    public function it_has_order_snaps()
    {
        OrderSnap::factory()->usingOrder($this->instance)->count(10)->create();

        $related = $this->instance->orderSnaps()->get();

        $this->assertCorrectRelation($related, OrderSnap::class);
    }

    /** @test */
    public function it_belongs_to_a_company()
    {
        $this->instance->company()->associate(Company::factory()->create());

        $related = $this->instance->company()->first();

        $this->assertInstanceOf(Company::class, $related);
    }
}

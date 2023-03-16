<?php

namespace Tests\Unit\Listeners\Order;

use App;
use App\Events\Order\LegacyApproved as ApprovedEvent;
use App\Listeners\Order\CreateOrderInvoice;
use App\Models\Order;
use App\Models\OrderInvoice;
use App\Models\Supplier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use ReflectionClass;
use Tests\TestCase;

class CreateOrderInvoiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(CreateOrderInvoice::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_creates_an_order_invoice()
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create([
            'bid_number' => $orderBidNumber = '123456',
            'name'       => $orderName = 'some order',
        ]);

        $event = new ApprovedEvent($order);

        $listener = App::make(CreateOrderInvoice::class);
        $listener->handle($event);

        $this->assertDatabaseHas(OrderInvoice::tableName(), [
            'order_id'      => $order->getKey(),
            'number'        => 1,
            'type'          => 'invoice',
            'subtotal'      => $order->subTotalWithDeliveryAndDiscount(),
            'take_rate'     => $supplier->take_rate,
            'bid_number'    => $orderBidNumber,
            'order_name'    => $orderName,
            'payment_terms' => '2.5%/10 Net 90',
        ]);
    }

    /** @test */
    public function it_creates_an_order_invoice_with_nullable_values()
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();

        $event = new ApprovedEvent($order);

        $listener = App::make(CreateOrderInvoice::class);
        $listener->handle($event);

        $this->assertDatabaseHas(OrderInvoice::tableName(), [
            'order_id'      => $order->getKey(),
            'number'        => 1,
            'type'          => 'invoice',
            'subtotal'      => $order->subTotalWithDeliveryAndDiscount(),
            'take_rate'     => $supplier->take_rate,
            'bid_number'    => null,
            'order_name'    => null,
            'payment_terms' => '2.5%/10 Net 90',
        ]);
    }

    /** @test */
    public function it_creates_order_invoices_for_the_same_supplier_in_the_same_month()
    {
        $supplier     = Supplier::factory()->createQuietly();
        $order        = Order::factory()->usingSupplier($supplier)->create();
        $anotherOrder = Order::factory()->usingSupplier($supplier)->create();

        $listener = App::make(CreateOrderInvoice::class);

        $event = new ApprovedEvent($order);
        $listener->handle($event);

        $anotherEvent = new ApprovedEvent($anotherOrder);
        $listener->handle($anotherEvent);

        $this->assertDatabaseHas(OrderInvoice::tableName(), [
            'order_id'      => $order->getKey(),
            'number'        => 1,
            'type'          => 'invoice',
            'subtotal'      => $order->subTotalWithDeliveryAndDiscount(),
            'take_rate'     => $supplier->take_rate,
            'bid_number'    => null,
            'order_name'    => null,
            'payment_terms' => '2.5%/10 Net 90',
        ]);

        $this->assertDatabaseHas(OrderInvoice::tableName(), [
            'order_id'      => $anotherOrder->getKey(),
            'number'        => 1,
            'type'          => 'invoice',
            'subtotal'      => $anotherOrder->subTotalWithDeliveryAndDiscount(),
            'take_rate'     => $supplier->take_rate,
            'bid_number'    => null,
            'order_name'    => null,
            'payment_terms' => '2.5%/10 Net 90',
        ]);
    }

    /** @test */
    public function it_creates_order_invoices_for_different_suppliers_in_the_same_month()
    {
        $orders      = Order::factory()->count(2)->createQuietly();
        $firstOrder  = $orders->first();
        $secondOrder = $orders->last();

        $listener = App::make(CreateOrderInvoice::class);

        $event = new ApprovedEvent($firstOrder);
        $listener->handle($event);

        $anotherEvent = new ApprovedEvent($secondOrder);
        $listener->handle($anotherEvent);

        $this->assertDatabaseHas(OrderInvoice::tableName(), [
            'order_id'      => $firstOrder->getKey(),
            'number'        => 1,
            'type'          => 'invoice',
            'subtotal'      => $firstOrder->subTotalWithDeliveryAndDiscount(),
            'take_rate'     => $firstOrder->supplier->take_rate,
            'bid_number'    => null,
            'order_name'    => null,
            'payment_terms' => '2.5%/10 Net 90',
        ]);

        $this->assertDatabaseHas(OrderInvoice::tableName(), [
            'order_id'      => $secondOrder->getKey(),
            'number'        => 2,
            'type'          => 'invoice',
            'subtotal'      => $secondOrder->subTotalWithDeliveryAndDiscount(),
            'take_rate'     => $secondOrder->supplier->take_rate,
            'bid_number'    => null,
            'order_name'    => null,
            'payment_terms' => '2.5%/10 Net 90',
        ]);
    }

    /** @test */
    public function it_creates_order_invoices_for_the_same_supplier_in_different_months()
    {
        $supplier = Supplier::factory()->createQuietly();
        $listener = App::make(CreateOrderInvoice::class);

        Carbon::setTestNow('2022-05-08 00:00:00');
        $order = Order::factory()->usingSupplier($supplier)->create(['created_at' => Carbon::now()->subMonth()]);
        $event = new ApprovedEvent($order);
        $listener->handle($event);

        Carbon::setTestNow('2022-06-08 00:00:00');
        $anotherOrder = Order::factory()->usingSupplier($supplier)->create();
        $anotherEvent = new ApprovedEvent($anotherOrder);
        $listener->handle($anotherEvent);

        $this->assertDatabaseHas(OrderInvoice::tableName(), [
            'order_id'      => $order->getKey(),
            'number'        => 1,
            'type'          => 'invoice',
            'subtotal'      => $order->subTotalWithDeliveryAndDiscount(),
            'take_rate'     => $supplier->take_rate,
            'bid_number'    => null,
            'order_name'    => null,
            'payment_terms' => '2.5%/10 Net 90',
        ]);

        $this->assertDatabaseHas(OrderInvoice::tableName(), [
            'order_id'      => $anotherOrder->getKey(),
            'number'        => 2,
            'type'          => 'invoice',
            'subtotal'      => $anotherOrder->subTotalWithDeliveryAndDiscount(),
            'take_rate'     => $supplier->take_rate,
            'bid_number'    => null,
            'order_name'    => null,
            'payment_terms' => '2.5%/10 Net 90',
        ]);
    }
}

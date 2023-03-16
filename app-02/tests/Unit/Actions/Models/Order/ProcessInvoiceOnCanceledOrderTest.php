<?php

namespace Tests\Unit\Actions\Models\Order;

use App\Actions\Models\Order\ProcessInvoiceOnCanceledOrder;
use App\Models\Order;
use App\Models\OrderInvoice;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessInvoiceOnCanceledOrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_does_nothing_if_the_order_is_not_canceled()
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->pending()->usingSupplier($supplier)->create();

        (new ProcessInvoiceOnCanceledOrder($order))->execute();

        $this->expectNotToPerformAssertions();
    }

    /** @test */
    public function it_does_nothing_if_the_order_has_no_invoice()
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->canceled()->usingSupplier($supplier)->create();

        (new ProcessInvoiceOnCanceledOrder($order))->execute();

        $this->expectNotToPerformAssertions();
    }

    /** @test */
    public function it_does_nothing_if_the_order_has_a_credit()
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->canceled()->usingSupplier($supplier)->create();
        OrderInvoice::factory()->invoice()->usingOrder($order)->create();
        OrderInvoice::factory()->credit()->usingOrder($order)->create();

        (new ProcessInvoiceOnCanceledOrder($order))->execute();

        $this->expectNotToPerformAssertions();
    }

    /** @test */
    public function it_creates_a_credit_without_deletes_the_invoice_if_it_is_processed()
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->canceled()->usingSupplier($supplier)->create();
        $invoice  = OrderInvoice::factory()->invoice()->processed()->usingOrder($order)->create();

        (new ProcessInvoiceOnCanceledOrder($order))->execute();

        $this->assertDatabaseHas(OrderInvoice::tableName(), [
            'id'       => $invoice->getKey(),
            'type'     => 'invoice',
            'order_id' => $order->getKey(),
        ]);

        $this->assertDatabaseHas(OrderInvoice::tableName(), [
            'type'     => 'credit',
            'order_id' => $order->getKey(),
        ]);
    }

    /** @test */
    public function it_creates_a_credit_with_the_same_invoice_info_excepts_some_fields()
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->canceled()->usingSupplier($supplier)->create();
        $invoice  = OrderInvoice::factory()->invoice()->processed()->usingOrder($order)->create([
            'created_at' => Carbon::now()->subMonth(),
            'updated_at' => Carbon::now()->subMonth(),
        ])->refresh();

        (new ProcessInvoiceOnCanceledOrder($order))->execute();

        $fieldsThatMustHaveDifferentValues = [
            'id',
            'type',
            'processed_at',
            'created_at',
            'updated_at',
        ];

        $credit = $order->credit()->first();

        $this->assertEqualsCanonicalizing(collect($invoice)->except($fieldsThatMustHaveDifferentValues),
            collect($credit)->except($fieldsThatMustHaveDifferentValues));
        $this->assertNotEqualsCanonicalizing(collect($invoice)->only($fieldsThatMustHaveDifferentValues),
            collect($credit)->only($fieldsThatMustHaveDifferentValues));
    }

    /** @test */
    public function it_deletes_the_invoice_and_does_not_create_a_credit_if_the_invoice_is_not_processed()
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->canceled()->usingSupplier($supplier)->create();
        $invoice  = OrderInvoice::factory()->invoice()->notProcessed()->usingOrder($order)->create();

        (new ProcessInvoiceOnCanceledOrder($order))->execute();
        (new ProcessInvoiceOnCanceledOrder($order))->execute();

        $this->assertDatabaseMissing(OrderInvoice::tableName(), [
            'id'       => $invoice->getKey(),
            'type'     => 'invoice',
            'order_id' => $order->getKey(),
        ]);

        $this->assertDatabaseMissing(OrderInvoice::tableName(), [
            'type'     => 'credit',
            'order_id' => $order->getKey(),
        ]);
    }
}

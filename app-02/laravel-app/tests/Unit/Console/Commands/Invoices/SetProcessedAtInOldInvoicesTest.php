<?php

namespace Tests\Unit\Console\Commands\Invoices;

use App\Console\Commands\Invoices\SetProcessedAtInOldInvoices;
use App\Models\Order;
use App\Models\OrderInvoice;
use App\Models\Supplier;
use Config;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/** @see SetProcessedAtInOldInvoices */
class SetProcessedAtInOldInvoicesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_processes_unprocessed_old_order_invoices()
    {
        Config::set('scheduler.export-order-invoices.day', 8);
        Carbon::setTestNow('2022-09-15 00:00:00');

        $supplier = Supplier::factory()->createQuietly();
        OrderInvoice::factory()->sequence(function(Sequence $sequence) use ($supplier) {
            return [
                'order_id'   => Order::factory()->usingSupplier($supplier)->create(),
                'created_at' => Carbon::now()->subMonths($sequence->index + 1),
            ];
        })->count(3)->notProcessed()->create();

        $this->artisan('invoices:set-processed-at-for-old-invoices')->assertSuccessful();

        $this->assertDatabaseMissing(OrderInvoice::tableName(), ['processed_at' => null]);
    }

    /** @test */
    public function it_does_not_process_the_previous_month_order_invoices_if_the_processing_date_has_not_yet_passed()
    {
        Config::set('scheduler.export-order-invoices.day', 8);
        Carbon::setTestNow('2022-09-05 00:00:00');

        $supplier                  = Supplier::factory()->createQuietly();
        $notProcessedPreviousMonth = OrderInvoice::factory()->sequence(function() use ($supplier) {
            return [
                'order_id'   => Order::factory()->usingSupplier($supplier)->create(),
                'created_at' => Carbon::now()->subMonth(),
            ];
        })->count(3)->notProcessed()->create();
        OrderInvoice::factory()->sequence(function() use ($supplier) {
            return [
                'order_id'   => Order::factory()->usingSupplier($supplier)->create(),
                'created_at' => Carbon::now()->subMonths(2),
            ];
        })->count(2)->notProcessed()->create();

        $this->artisan('invoices:set-processed-at-for-old-invoices')->assertSuccessful();

        foreach ($notProcessedPreviousMonth as $orderInvoice) {
            $orderInvoice->refresh();
            $this->assertNull($orderInvoice->processed_at);
        }
    }

    /** @test */
    public function it_does_not_change_the_processed_at_date_of_already_processed_invoice_orders()
    {
        Config::set('scheduler.export-order-invoices.day', 8);
        Carbon::setTestNow('2022-09-05 00:00:00');

        $supplier  = Supplier::factory()->createQuietly();
        $processed = OrderInvoice::factory()->sequence(function() use ($supplier) {
            return [
                'order_id'   => Order::factory()->usingSupplier($supplier)->create(),
                'created_at' => Carbon::now()->subMonths(2),
            ];
        })->count(3)->processed()->create();
        OrderInvoice::factory()->sequence(function() use ($supplier) {
            return [
                'order_id'   => Order::factory()->usingSupplier($supplier)->create(),
                'created_at' => Carbon::now()->subMonths(3),
            ];
        })->count(2)->notProcessed()->create();

        $this->artisan('invoices:set-processed-at-for-old-invoices')->assertSuccessful();

        foreach ($processed as $orderInvoice) {
            $previousProcessedAt = $orderInvoice->processed_at;
            $orderInvoice->refresh();
            $currentProcessedAt = $orderInvoice->processed_at;
            $this->assertEquals($previousProcessedAt, $currentProcessedAt);
        }
    }
}

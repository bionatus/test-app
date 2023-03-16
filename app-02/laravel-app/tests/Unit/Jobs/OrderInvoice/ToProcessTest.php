<?php

namespace Tests\Unit\Jobs\OrderInvoice;

use App\Jobs\OrderInvoice\SendExportEmail;
use App\Jobs\OrderInvoice\ToProcess;
use App\Models\Order;
use App\Models\OrderInvoice;
use App\Models\OrderInvoice\Scopes\NotProcessed;
use App\Models\Supplier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use ReflectionClass;
use Tests\TestCase;

class ToProcessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SendExportEmail::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_processes_the_not_processed_order_invoices_from_the_previous_month()
    {
        $supplier = Supplier::factory()->createQuietly();
        OrderInvoice::factory()->sequence(function() use ($supplier) {
            return [
                'order_id'   => Order::factory()->usingSupplier($supplier)->create(),
                'created_at' => Carbon::now()->subMonth(),
            ];
        })->count(3)->notProcessed()->create();

        (new ToProcess())->handle();

        $this->assertDatabaseMissing(OrderInvoice::tableName(), ['processed_at' => null]);
    }

    /** @test */
    public function it_does_not_process_the_not_processed_order_invoices_from_the_others_months()
    {
        $supplier             = Supplier::factory()->createQuietly();
        $expectedNotProcessed = OrderInvoice::factory()->sequence(function(Sequence $sequence) use ($supplier) {
            return [
                'order_id'   => Order::factory()->usingSupplier($supplier)->create(),
                'created_at' => Carbon::now()->subMonths($sequence->index + 3),
            ];
        })->count(3)->notProcessed()->create();
        OrderInvoice::factory()->sequence(function() use ($supplier) {
            return [
                'order_id'   => Order::factory()->usingSupplier($supplier)->create(),
                'created_at' => Carbon::now()->subMonth(),
            ];
        })->count(2)->notProcessed()->create();
        OrderInvoice::factory()->sequence(function(Sequence $sequence) use ($supplier) {
            return [
                'order_id'   => Order::factory()->usingSupplier($supplier)->create(),
                'created_at' => Carbon::now()->subMonths($sequence->index + 1),
            ];
        })->count(3)->processed()->create();

        (new ToProcess())->handle();

        $notProcessed             = OrderInvoice::scoped(new NotProcessed())->get();
        $notProcessedKeys         = $notProcessed->pluck(OrderInvoice::keyName());
        $expectedNotProcessedKeys = $expectedNotProcessed->pluck(OrderInvoice::keyName());

        $this->assertEquals($expectedNotProcessedKeys, $notProcessedKeys);
    }

    /** @test */
    public function it_does_not_change_the_processed_at_date_of_already_processed_previous_month_invoice_orders()
    {
        $supplier  = Supplier::factory()->createQuietly();
        $processed = OrderInvoice::factory()->sequence(function() use ($supplier) {
            return [
                'order_id'   => Order::factory()->usingSupplier($supplier)->create(),
                'created_at' => Carbon::now()->subMonth(),
            ];
        })->count(3)->processed()->create();
        OrderInvoice::factory()->sequence(function() use ($supplier) {
            return [
                'order_id'   => Order::factory()->usingSupplier($supplier)->create(),
                'created_at' => Carbon::now()->subMonth(),
            ];
        })->count(3)->notProcessed()->create();

        (new ToProcess())->handle();

        foreach ($processed as $orderInvoice) {
            $previousProcessedAt = $orderInvoice->processed_at;
            $orderInvoice->refresh();
            $currentProcessedAt = $orderInvoice->processed_at;
            $this->assertEquals($previousProcessedAt, $currentProcessedAt);
        }
    }
}

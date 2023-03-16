<?php

namespace Tests\Unit\Models\Supplier\Scopes;

use App\Models\OrderInvoice;
use App\Models\Supplier;
use App\Models\Supplier\Scopes\ByOrderInvoiceCreationMonth;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByOrderInvoiceCreationMonthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_order_invoices_created_month()
    {
        Carbon::setTestNow('2022-06-08 00:00:00');
        $expectedCreatedAt = Carbon::now()->subMonth();
        $orderInvoices     = OrderInvoice::factory()->count(3)->createQuietly(['created_at' => $expectedCreatedAt]);
        OrderInvoice::factory()->count(2)->createQuietly(['created_at' => Carbon::now()]);
        $expectedSuppliers = $orderInvoices->pluck('order.supplier');

        $filtered = Supplier::scoped(new ByOrderInvoiceCreationMonth($expectedCreatedAt->month))->get();

        $this->assertCount($expectedSuppliers->count(), $filtered);
        $expectedSuppliers->each(function(Supplier $supplier) use ($filtered) {
            $this->assertTrue($filtered->contains($supplier));
        });
    }
}

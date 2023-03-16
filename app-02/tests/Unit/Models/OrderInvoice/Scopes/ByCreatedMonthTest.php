<?php

namespace Tests\Unit\Models\OrderInvoice\Scopes;

use App\Models\OrderInvoice;
use App\Models\OrderInvoice\Scopes\ByCreatedMonth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ByCreatedMonthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_created_month()
    {
        Carbon::setTestNow('2022-06-08 00:00:00');
        $createdAt = Carbon::now()->subMonth();
        $expected  = OrderInvoice::factory()->count(3)->createQuietly(['created_at' => $createdAt]);
        OrderInvoice::factory()->count(2)->createQuietly();

        $filtered = OrderInvoice::scoped(new ByCreatedMonth($createdAt->month))->get();

        $this->assertCount($expected->count(), $filtered);
        $expected->each(function(OrderInvoice $orderInvoice) use ($filtered) {
            $this->assertTrue($filtered->contains($orderInvoice));
        });
    }
}

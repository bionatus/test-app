<?php

namespace Tests\Unit\Models\OrderInvoice\Scopes;

use App\Models\OrderInvoice;
use App\Models\OrderInvoice\Scopes\ByCreatedBetween;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ByCreatedBetweenTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_created_at_between_two_dates()
    {
        Carbon::setTestNow('2022-06-08 00:00:00');
        $now = Carbon::now();
        OrderInvoice::factory()->count(1)->createQuietly(['created_at' => $now->clone()->subMonth()]);
        OrderInvoice::factory()->count(2)->createQuietly(['created_at' => $now->clone()->subMonth()->startOfMonth()]);
        OrderInvoice::factory()->count(3)->createQuietly(['created_at' => $now->clone()->subMonth()->endOfMonth()]);
        $expected = OrderInvoice::all();

        OrderInvoice::factory()->count(2)->createQuietly();

        $fromDate = Carbon::now()->subMonth()->startOfMonth()->toDateTimeString();
        $tillDate = Carbon::now()->subMonth()->endOfMonth()->toDateTimeString();

        $filtered = OrderInvoice::scoped(new ByCreatedBetween($fromDate, $tillDate))->get();

        $this->assertCount($expected->count(), $filtered);
        $expected->each(function(OrderInvoice $orderInvoice) use ($filtered) {
            $this->assertTrue($filtered->contains($orderInvoice));
        });
    }
}

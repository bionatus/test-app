<?php

namespace Tests\Unit\Models\OrderInvoice\Scopes;

use App\Models\OrderInvoice;
use App\Models\OrderInvoice\Scopes\NotProcessed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotProcessedTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_not_processed()
    {
        $expected = OrderInvoice::factory()->notProcessed()->count(3)->createQuietly();
        OrderInvoice::factory()->processed()->count(2)->createQuietly();

        $filtered     = OrderInvoice::scoped(new NotProcessed())->get();
        $expectedKeys = $expected->pluck(OrderInvoice::keyName());
        $filteredKeys = $filtered->pluck(OrderInvoice::keyName());

        $this->assertCount($expected->count(), $filtered);
        $this->assertEqualsCanonicalizing($expectedKeys, $filteredKeys);
    }
}

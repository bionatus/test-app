<?php

namespace Tests\Unit\Models\Supplier\Scopes;

use App\Models\Supplier;
use App\Models\Supplier\Scopes\Published;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PublishedTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_orders_by_supplier_published_at()
    {
        Supplier::factory()->count(10)->createQuietly();
        Supplier::factory()->count($publishedCount = 20)->createQuietly(['published_at' => Carbon::now()]);

        $orderedSuppliers = Supplier::scoped(new Published())->get();

        $orderedSuppliers->take($publishedCount)->each(function(Supplier $supplier) {
            $this->assertNotNull($supplier->published_at);
        });

        $orderedSuppliers->skip($publishedCount)->each(function(Supplier $supplier) {
            $this->assertNull($supplier->published_at);
        });
    }
}

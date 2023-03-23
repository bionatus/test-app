<?php

namespace Tests\Unit\Models\ItemOrder\Scopes;

use App\Models\ItemOrder;
use App\Models\ItemOrder\Scopes\ByInitialRequest;
use App\Models\Order;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByInitialRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_initial_request()
    {
        $supplier = Supplier::factory()->createQuietly();
        $order    = Order::factory()->usingSupplier($supplier)->create();
        ItemOrder::factory()->usingOrder($order)->count(3)->create();
        ItemOrder::factory()->usingOrder($order)->notInitialRequest()->count(2)->create();

        $filtered = ItemOrder::scoped(new ByInitialRequest(false))->get();
        $this->assertCount(2, $filtered);
    }
}

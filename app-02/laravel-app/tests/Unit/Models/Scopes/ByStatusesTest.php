<?php

namespace Tests\Unit\Models\Scopes;

use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\Scopes\ByStatuses;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByStatusesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_statuses_on_item_order_model()
    {
        $supplier  = Supplier::factory()->createQuietly();
        $available = ItemOrder::factory()->available()->count(3)->sequence(fn() => [
            'order_id' => Order::factory()->usingSupplier($supplier)->create(),
        ])->create();
        $pending   = ItemOrder::factory()->pending()->count(2)->sequence(fn() => [
            'order_id' => Order::factory()->usingSupplier($supplier)->create(),
        ])->create();
        ItemOrder::factory()->notAvailable()->count(2)->sequence(fn() => [
            'order_id' => Order::factory()->usingSupplier($supplier)->create(),
        ])->create();
        ItemOrder::factory()->seeBelowItem()->count(2)->sequence(fn() => [
            'order_id' => Order::factory()->usingSupplier($supplier)->create(),
        ])->create();
        ItemOrder::factory()->removed()->count(2)->sequence(fn() => [
            'order_id' => Order::factory()->usingSupplier($supplier)->create(),
        ])->create();

        $filtered = ItemOrder::scoped(new ByStatuses([ItemOrder::STATUS_AVAILABLE, ItemOrder::STATUS_PENDING]))->get();
        $expected = $available->concat($pending);

        $this->assertCount($expected->count(), $filtered);
        $filtered->each(function(ItemOrder $itemOrder) use ($expected) {
            $this->assertSame($expected->shift()->getKey(), $itemOrder->getKey());
        });
    }
}

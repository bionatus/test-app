<?php

namespace Tests\Unit\Models\Scopes;

use App\Models\Item;
use App\Models\Order;
use App\Models\OrderInvoice;
use App\Models\Post;
use App\Models\Scopes\ByType;
use App\Models\Staff;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByTypeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_type_on_item_model()
    {
        $expectedItems = Item::factory()->part()->count(3)->create();
        Item::factory()->supply()->count(2)->create();

        $filtered = Item::scoped(new ByType(Item::TYPE_PART))->get();

        $this->assertEqualsCanonicalizing($expectedItems->modelKeys(), $filtered->modelKeys());
    }

    /** @test */
    public function it_filters_by_type_on_order_invoice_model()
    {
        $supplier      = Supplier::factory()->createQuietly();
        $expectedItems = OrderInvoice::factory()->credit()->count(3)->sequence(fn() => [
            'order_id' => Order::factory()->usingSupplier($supplier)->create(),
        ])->create();
        OrderInvoice::factory()->invoice()->count(2)->sequence(fn() => [
            'order_id' => Order::factory()->usingSupplier($supplier)->create(),
        ])->create();

        $filtered = OrderInvoice::scoped(new ByType(OrderInvoice::TYPE_CREDIT))->get();

        $this->assertEqualsCanonicalizing($expectedItems->modelKeys(), $filtered->modelKeys());
    }

    /** @test */
    public function it_filters_by_type_on_post_model()
    {
        $expectedItems = Post::factory()->needsHelp()->count(3)->create();
        Post::factory()->funny()->count(2)->create();
        Post::factory()->other()->count(2)->create();

        $filtered = Post::scoped(new ByType(Post::TYPE_NEEDS_HELP))->get();

        $this->assertEqualsCanonicalizing($expectedItems->modelKeys(), $filtered->modelKeys());
    }

    /** @test */
    public function it_filters_by_type_on_staff_model()
    {
        $expectedStaff = Staff::factory()->manager()->count(3)->createQuietly();
        Staff::factory()->contact()->count(2)->createQuietly();
        Staff::factory()->counter()->count(2)->createQuietly();

        $filtered = Staff::scoped(new ByType(Staff::TYPE_MANAGER))->get();

        $this->assertEqualsCanonicalizing($expectedStaff->modelKeys(), $filtered->modelKeys());
    }
}

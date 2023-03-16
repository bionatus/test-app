<?php

namespace Tests\Unit\Policies\LiveApi\V1\Order;

use App\Models\CustomItem;
use App\Models\ItemOrder;
use App\Models\Order;
use App\Models\Staff;
use App\Policies\LiveApi\V1\OrderPolicy;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateCustomItemTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_allows_to_create_custom_item()
    {
        $order = Order::factory()->createQuietly();

        $policy = new OrderPolicy();

        $this->assertTrue($policy->createCustomItem(Staff::factory()->createQuietly(), $order));
    }

    /**
     * @test
     */
    public function it_disallows_to_create_custom_item()
    {
        $order       = Order::factory()->createQuietly();
        $customItems = CustomItem::factory()->count(10)->create();
        ItemOrder::factory()->usingOrder($order)->count(10)->sequence(fn(Sequence $sequence
        ) => ['item_id' => $customItems->get($sequence->index)->item->getKey()])->create();

        $policy = new OrderPolicy();
        $this->assertFalse($policy->createCustomItem(Staff::factory()->createQuietly(), $order));
    }
}

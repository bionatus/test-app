<?php

namespace Tests\Unit\Models;

use App\Models\CartItem;
use Str;

class CartItemTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(CartItem::tableName(), [
            'id',
            'uuid',
            'item_id',
            'cart_id',
            'quantity',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_uses_uuid()
    {
        $quote = CartItem::factory()->createQuietly(['uuid' => Str::uuid()->toString()]);

        $this->assertEquals($quote->uuid, $quote->getRouteKey());
    }

    /** @test */
    public function it_fills_uuid_on_creation()
    {
        $cartItem = CartItem::factory()->make(['uuid' => null]);
        $cartItem->save();

        $this->assertNotNull($cartItem->uuid);
    }

    /** @test */
    public function it_will_not_log_activity_on_create()
    {
        $cartItem = CartItem::factory()->createQuietly();
        $this->assertEquals(0, $cartItem->activities->count());
    }

    /** @test */
    public function it_will_log_activity_on_delete()
    {
        $cartItem = CartItem::factory()->createQuietly();
        $cartItem->delete();

        $this->assertEquals(1, $cartItem->activities->count());
        $this->assertDatabaseHas('activity_log', [
            'log_name'     => 'cart_item_log',
            'description'  => 'cart_item.deleted',
            'subject_type' => 'cart_item',
            'subject_id'   => $cartItem->getKey(),
            'resource'     => 'cart_item',
            'event'        => 'deleted',
        ]);
    }
}

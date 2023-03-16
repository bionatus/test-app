<?php

namespace Tests\Unit\Rules\CartItem;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Item;
use App\Rules\CartItem\UniqueItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniqueItemTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_false_if_value_already_exists_on_cart()
    {
        $cartItem = CartItem::factory()->create();
        $item     = $cartItem->item;

        $rule = new UniqueItem($cartItem->cart);

        $this->assertFalse($rule->passes('', $item->getRouteKey()));
    }

    /** @test */
    public function it_returns_true_if_value_is_not_in_the_cart()
    {
        $cart = Cart::factory()->create();
        $item = Item::factory()->create();

        $rule = new UniqueItem($cart);

        $this->assertTrue($rule->passes('', $item->getRouteKey()));
    }

    /** @test */
    public function it_has_specific_error_message()
    {
        $cartItem = CartItem::factory()->create();

        $expectedMessage = 'This :attribute already exists on the cart';
        $rule            = new UniqueItem($cartItem->cart);

        $this->assertEquals($expectedMessage, $rule->message());
    }
}

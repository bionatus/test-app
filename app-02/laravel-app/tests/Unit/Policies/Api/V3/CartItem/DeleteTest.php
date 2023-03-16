<?php

namespace Tests\Unit\Policies\Api\V3\CartItem;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use App\Policies\Api\V3\CartItemPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_a_owner_to_delete_a_cart_item()
    {
        $owner    = User::factory()->create();
        $cart     = Cart::factory()->usingUser($owner)->create();
        $cartItem = CartItem::factory()->usingCart($cart)->create();

        $policy = new CartItemPolicy();

        $this->assertTrue($policy->delete($owner, $cartItem));
    }

    /** @test */
    public function it_disallows_another_user_to_delete_a_cart_item()
    {
        $notOwner = User::factory()->create();
        $cart     = Cart::factory()->create();
        $cartItem = CartItem::factory()->usingCart($cart)->create();

        $policy = new CartItemPolicy();

        $this->assertFalse($policy->delete($notOwner, $cartItem));
    }
}

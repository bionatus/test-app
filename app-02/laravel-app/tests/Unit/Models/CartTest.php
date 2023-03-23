<?php

namespace Tests\Unit\Models;

use App\Models\Cart;
use App\Models\User;

class CartTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Cart::tableName(), [
            'id',
            'user_id',
            'supplier_id',
            'created_at',
            'updated_at',
        ]);
    }

    /** @test */
    public function it_knows_if_a_user_is_its_owner()
    {
        $notOwner = User::factory()->create();
        $owner    = User::factory()->create();

        $cart = Cart::factory()->usingUser($owner)->create();

        $this->assertFalse($cart->isOwner($notOwner));
        $this->assertTrue($cart->isOwner($owner));
    }
}

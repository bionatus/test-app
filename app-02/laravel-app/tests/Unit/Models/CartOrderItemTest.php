<?php

namespace Tests\Unit\Models;

use App\Models\CartOrderItem;

class CartOrderItemTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(CartOrderItem::tableName(), [
            'id',
            'cart_order_id',
            'item_id',
            'quantity',
            'created_at',
            'updated_at',
        ]);
    }
}

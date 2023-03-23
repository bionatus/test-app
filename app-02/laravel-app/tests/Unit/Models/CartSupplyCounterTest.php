<?php

namespace Tests\Unit\Models;

use App\Models\CartSupplyCounter;

class CartSupplyCounterTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(CartSupplyCounter::tableName(), [
            'id',
            'user_id',
            'supply_id',
            'created_at',
            'updated_at',
        ]);
    }
}

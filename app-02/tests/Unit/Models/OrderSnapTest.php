<?php

namespace Tests\Unit\Models;

use App\Models\OrderSnap;

class OrderSnapTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(OrderSnap::tableName(), [
            'id',
            'order_id',
            'user_id',
            'supplier_id',
            'oem_id',
            'name',
            'working_on_it',
            'status',
            'bid_number',
            'discount',
            'tax',
            'created_at',
            'updated_at',
        ]);
    }
}

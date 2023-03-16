<?php

namespace Tests\Unit\Models;

use App\Models\ItemOrderSnap;

class ItemOrderSnapTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(ItemOrderSnap::tableName(), [
            'id',
            'order_snap_id',
            'item_id',
            'order_id',
            'replacement_id',
            'quantity',
            'price',
            'supply_detail',
            'custom_detail',
            'generic_part_description',
            'status',
            'created_at',
            'updated_at',
        ]);
    }
}

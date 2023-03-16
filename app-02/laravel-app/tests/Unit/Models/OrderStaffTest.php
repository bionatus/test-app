<?php

namespace Tests\Unit\Models;

use App\Models\OrderStaff;

class OrderStaffTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(OrderStaff::tableName(), [
            'id',
            'order_id',
            'staff_id',
            'created_at',
            'updated_at',
        ]);
    }
}

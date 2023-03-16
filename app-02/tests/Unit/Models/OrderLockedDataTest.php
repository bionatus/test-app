<?php

namespace Tests\Unit\Models;

use App\Models\OrderLockedData;

class OrderLockedDataTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(OrderLockedData::tableName(), [
            'id',
            'order_id',
            'user_first_name',
            'user_last_name',
            'user_company',
            'channel',
            'created_at',
            'updated_at',
        ]);
    }
}

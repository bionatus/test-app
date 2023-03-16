<?php

namespace Tests\Unit\Models;

use App\Models\SharedOrder;

class SharedOrderTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(SharedOrder::tableName(), [
            'id',
            'user_id',
            'order_id',
            'created_at',
            'updated_at',
        ]);
    }
}

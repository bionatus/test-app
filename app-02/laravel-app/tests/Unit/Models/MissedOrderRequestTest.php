<?php

namespace Tests\Unit\Models;

use App\Models\MissedOrderRequest;

class MissedOrderRequestTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(MissedOrderRequest::tableName(), [
            'id',
            'order_id',
            'missed_supplier_id',
            'created_at',
            'updated_at',
        ]);
    }
}

<?php

namespace Tests\Unit\Models;

use App\Models\SupplierHour;

class SupplierHourTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(SupplierHour::tableName(), [
            'id',
            'supplier_id',
            'day',
            'from',
            'to',
            'created_at',
            'updated_at',
        ]);
    }
}

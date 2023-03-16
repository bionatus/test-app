<?php

namespace Tests\Unit\Models;

use App\Models\ApiUsage;

class ApiUsageTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(ApiUsage::tableName(), [
            'id',
            'user_id',
            'supplier_id',
            'date',
            'created_at',
            'updated_at',
        ]);
    }
}

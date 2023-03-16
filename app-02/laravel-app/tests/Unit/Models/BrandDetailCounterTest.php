<?php

namespace Tests\Unit\Models;

use App\Models\BrandDetailCounter;

class BrandDetailCounterTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(BrandDetailCounter::tableName(), [
            'id',
            'brand_id',
            'user_id',
            'staff_id',
            'created_at',
            'updated_at',
        ]);
    }
}

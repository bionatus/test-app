<?php

namespace Tests\Unit\Models;

use App\Models\OemDetailCounter;

class OemDetailCounterTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(OemDetailCounter::tableName(), [
            'id',
            'oem_id',
            'oem_search_counter_id',
            'staff_id',
            'user_id',
            'created_at',
            'updated_at',
        ]);
    }
}

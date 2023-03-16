<?php

namespace Tests\Unit\Models;

use App\Models\PartDetailCounter;

class PartDetailCounterTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(PartDetailCounter::tableName(), [
            'id',
            'part_id',
            'part_search_counter_id',
            'staff_id',
            'user_id',
            'created_at',
            'updated_at',
        ]);
    }
}

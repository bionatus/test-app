<?php

namespace Tests\Unit\Models;

use App\Models\OemSearchCounter;

class OemSearchCounterTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(OemSearchCounter::tableName(), [
            'id',
            'uuid',
            'staff_id',
            'user_id',
            'criteria',
            'results',
            'created_at',
            'updated_at',
        ]);
    }
}

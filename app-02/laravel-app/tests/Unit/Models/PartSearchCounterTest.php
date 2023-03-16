<?php

namespace Tests\Unit\Models;

use App\Models\PartSearchCounter;

class PartSearchCounterTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(PartSearchCounter::tableName(), [
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

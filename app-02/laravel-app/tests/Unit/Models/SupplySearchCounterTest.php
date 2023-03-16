<?php

namespace Tests\Unit\Models;

use App\Models\SupplySearchCounter;

class SupplySearchCounterTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(SupplySearchCounter::tableName(), [
            'id',
            'uuid',
            'user_id',
            'criteria',
            'results',
            'created_at',
            'updated_at',
        ]);
    }
}

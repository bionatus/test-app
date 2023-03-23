<?php

namespace Tests\Unit\Models;

use App\Models\StateTimezone;

class StateTimezoneTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(StateTimezone::tableName(), [
            'id',
            'country',
            'state',
            'timezone',
            'created_at',
            'updated_at',
        ]);
    }
}

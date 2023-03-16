<?php

namespace Tests\Unit\Models;

use App\Models\ZipTimezone;

class ZipTimezoneTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(ZipTimezone::tableName(), [
            'id',
            'country',
            'state',
            'county',
            'city',
            'zip',
            'timezone',
            'created_at',
            'updated_at',
        ]);
    }
}

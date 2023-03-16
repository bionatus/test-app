<?php

namespace Tests\Unit\Models;

use App\Models\Activity;

class ActivityTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Activity::tableName(), [
            'id',
            'log_name',
            'description',
            'subject_type',
            'subject_id',
            'causer_type',
            'causer_id',
            'properties',
            'resource',
            'event',
            'created_at',
            'updated_at',
        ]);
    }
}

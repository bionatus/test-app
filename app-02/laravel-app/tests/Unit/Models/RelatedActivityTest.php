<?php

namespace Tests\Unit\Models;

use App\Models\RelatedActivity;

class RelatedActivityTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(RelatedActivity::tableName(), [
            'id',
            'activity_id',
            'user_id',
            'resource',
            'event',
            'created_at',
            'updated_at',
        ]);
    }
}

<?php

namespace Tests\Unit\Models;

use App\Models\VideoElapsedTime;

class VideoElapsedTimeTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(VideoElapsedTime::tableName(), [
            'id',
            'user_id',
            'version',
            'seconds',
            'created_at',
            'updated_at',
        ]);
    }
}

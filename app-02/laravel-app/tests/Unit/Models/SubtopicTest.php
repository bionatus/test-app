<?php

namespace Tests\Unit\Models;

use App\Models\Subtopic;

class SubtopicTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Subtopic::tableName(), [
            'id',
            'topic_id',
        ]);
    }
}

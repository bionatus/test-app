<?php

namespace Tests\Unit\Models;

use App\Models\Topic;

class TopicTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Topic::tableName(), [
            'id',
            'description',
        ]);
    }
}

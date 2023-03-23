<?php

namespace Tests\Unit\Models;

use App\Models\SubjectTool;

class SubjectToolTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(SubjectTool::tableName(), [
            'id',
            'subject_id',
            'tool_id',
        ]);
    }
}

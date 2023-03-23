<?php

namespace Tests\Unit\Models;

use App\Models\PartNote;

class PartNoteTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(PartNote::tableName(), [
            'id',
            'part_id',
            'value',
        ]);
    }
}

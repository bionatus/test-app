<?php

namespace Tests\Unit\Models;

use App\Models\ReplacementNote;

class ReplacementNoteTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(ReplacementNote::tableName(), [
            'id',
            'replacement_id',
            'value',
        ]);
    }
}

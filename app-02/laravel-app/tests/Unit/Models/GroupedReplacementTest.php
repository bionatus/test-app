<?php

namespace Tests\Unit\Models;

use App\Models\GroupedReplacement;

class GroupedReplacementTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(GroupedReplacement::tableName(), [
            'id',
            'replacement_id',
            'replacement_part_id',
            'created_at',
            'updated_at',
        ]);
    }
}

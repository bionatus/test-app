<?php

namespace Tests\Unit\Models;

use App\Models\SingleReplacement;

class SingleReplacementTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(SingleReplacement::tableName(), [
            'id',
            'replacement_part_id',
        ]);
    }
}

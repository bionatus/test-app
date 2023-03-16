<?php

namespace Tests\Unit\Models;

use App\Models\RecommendedReplacement;

class RecommendedReplacementTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(RecommendedReplacement::tableName(), [
            'id',
            'supplier_id',
            'original_part_id',
            'brand',
            'part_number',
            'note',
            'created_at',
            'updated_at',
        ]);
    }
}

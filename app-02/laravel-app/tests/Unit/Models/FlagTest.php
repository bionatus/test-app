<?php

namespace Tests\Unit\Models;

use App\Models\Flag;

class FlagTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Flag::tableName(), [
            'id',
            'name',
            'flaggable_type',
            'flaggable_id',
            'created_at',
            'updated_at',
        ]);
    }
}

<?php

namespace Tests\Unit\Models;

use App\Models\Warning;

class WarningTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Warning::tableName(), [
            'id',
            'title',
            'description',
            'created_at',
            'updated_at',
        ]);
    }
}

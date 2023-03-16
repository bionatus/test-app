<?php

namespace Tests\Unit\Models;

use App\Models\OemPart;

class OemPartTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(OemPart::tableName(), [
            'id',
            'oem_id',
            'part_id',
            'uid',
            'created_at',
            'updated_at',
        ]);
    }
}

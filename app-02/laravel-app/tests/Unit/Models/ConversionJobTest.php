<?php

namespace Tests\Unit\Models;

use App\Models\ConversionJob;

class ConversionJobTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(ConversionJob::tableName(), [
            'id',
            'control',
            'standard',
            'optional',
            'created_at',
            'updated_at',
            'image',
            'retrofit',
        ]);
    }
}

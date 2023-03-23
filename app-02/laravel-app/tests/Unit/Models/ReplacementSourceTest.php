<?php

namespace Tests\Unit\Models;

use App\Models\ReplacementSource;

class ReplacementSourceTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(ReplacementSource::tableName(), [
            'id',
            'value',
        ]);
    }
}

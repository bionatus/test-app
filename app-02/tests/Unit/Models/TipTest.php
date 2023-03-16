<?php

namespace Tests\Unit\Models;

use App\Models\Tip;

class TipTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Tip::tableName(), [
            'id',
            'type',
            'description',
        ]);
    }
}

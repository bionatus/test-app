<?php

namespace Tests\Unit\Models;

use App\Models\Layout;

class LayoutTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Layout::tableName(),[
            'id',
            'version',
            'products',
            'conversion',
        ]);
    }
}

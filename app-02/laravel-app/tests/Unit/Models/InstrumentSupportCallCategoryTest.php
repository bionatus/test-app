<?php

namespace Tests\Unit\Models;

use App\Models\InstrumentSupportCallCategory;

class InstrumentSupportCallCategoryTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(InstrumentSupportCallCategory::tableName(), [
            'id',
            'instrument_id',
            'support_call_category_id',
            'created_at',
            'updated_at',
        ]);
    }
}

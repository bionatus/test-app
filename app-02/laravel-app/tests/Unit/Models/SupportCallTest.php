<?php

namespace Tests\Unit\Models;

use App\Models\SupportCall;

class SupportCallTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(SupportCall::tableName(), [
            'id',
            'uuid',
            'category',
            'subcategory',
            'user_id',
            'oem_id',
            'missing_oem_brand_id',
            'missing_oem_model_number',
            'created_at',
            'updated_at',
        ]);
    }
}

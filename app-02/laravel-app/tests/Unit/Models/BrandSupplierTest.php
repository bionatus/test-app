<?php

namespace Tests\Unit\Models;

use App\Models\BrandSupplier;

class BrandSupplierTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(BrandSupplier::tableName(), [
            'id',
            'brand_id',
            'supplier_id',
            'created_at',
            'updated_at',
        ]);
    }
}

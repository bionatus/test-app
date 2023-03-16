<?php

namespace Tests\Unit\Models;

use App\Models\SupplierCompany;

class SupplierCompanyTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(SupplierCompany::tableName(), [
            'id',
            'name',
            'email',
            'created_at',
            'updated_at',
        ]);
    }
}

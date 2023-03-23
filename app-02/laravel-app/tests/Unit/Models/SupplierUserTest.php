<?php

namespace Tests\Unit\Models;

use App\Models\SupplierUser;

class SupplierUserTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(SupplierUser::tableName(), [
            'id',
            'supplier_id',
            'user_id',
            'status',
            'customer_tier',
            'cash_buyer',
            'preferred',
            'visible_by_user',
            'created_at',
            'updated_at',
        ]);
    }
}

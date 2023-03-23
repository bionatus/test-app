<?php

namespace Tests\Unit\Models;

use App\Models\SupplierInvitation;

class SupplierInvitationTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(SupplierInvitation::tableName(), [
            'id',
            'supplier_id',
            'user_id',
            'created_at',
            'updated_at',
        ]);
    }
}

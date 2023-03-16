<?php

namespace Tests\Unit\Models;

use App\Models\PendingApprovalView;

class PendingApprovalViewTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(PendingApprovalView::tableName(), [
            'id',
            'order_id',
            'user_id',
            'created_at',
            'updated_at',
        ]);
    }
}

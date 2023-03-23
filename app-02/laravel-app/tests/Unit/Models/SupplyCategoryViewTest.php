<?php

namespace Tests\Unit\Models;

use App\Models\SupplyCategoryView;

class SupplyCategoryViewTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(SupplyCategoryView::tableName(), [
            'id',
            'user_id',
            'supply_category_id',
            'created_at',
            'updated_at',
        ]);
    }
}

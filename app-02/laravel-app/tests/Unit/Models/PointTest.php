<?php

namespace Tests\Unit\Models;

use App\Models\Point;

class PointTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Point::tableName(), [
            'id',
            'user_id',
            'object_type',
            'object_id',
            'action',
            'coefficient',
            'multiplier',
            'points_earned',
            'points_redeemed',
            'created_at',
            'updated_at',
        ]);
    }
}

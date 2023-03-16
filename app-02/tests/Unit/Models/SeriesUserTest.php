<?php

namespace Tests\Unit\Models;

use App\Models\SeriesUser;

class SeriesUserTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(SeriesUser::tableName(), [
            'id',
            'user_id',
            'series_id',
            'created_at',
            'updated_at',
        ]);
    }
}

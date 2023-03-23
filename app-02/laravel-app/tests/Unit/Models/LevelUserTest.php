<?php

namespace Tests\Unit\Models;

use App\Models\LevelUser;

class LevelUserTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(LevelUser::tableName(), [
            'id',
            'user_id',
            'level_id',
            'created_at',
            'updated_at',
        ]);
    }
}

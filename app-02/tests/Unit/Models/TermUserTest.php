<?php

namespace Tests\Unit\Models;

use App\Models\TermUser;

class TermUserTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(TermUser::tableName(), [
            'id',
            'user_id',
            'term_id',
            'created_at',
            'updated_at',
        ]);
    }
}

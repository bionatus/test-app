<?php

namespace Tests\Unit\Models;

use App\Models\Session;

class SessionTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Session::tableName(), [
            'id',
            'user_id',
            'subject_id',
            'ticket_id',
            'created_at',
            'updated_at',
        ]);
    }
}

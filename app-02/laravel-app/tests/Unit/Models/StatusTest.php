<?php

namespace Tests\Unit\Models;

use App\Models\Status;

class StatusTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Status::tableName(), [
            'id',
            'name',
            'slug',
            'created_at',
            'updated_at',
        ]);
    }
}

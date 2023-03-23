<?php

namespace Tests\Unit\Models;

use App\Models\OemUser;

class OemUserTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(OemUser::tableName(), [
            'id',
            'user_id',
            'oem_id',
            'created_at',
            'updated_at',
        ]);
    }
}

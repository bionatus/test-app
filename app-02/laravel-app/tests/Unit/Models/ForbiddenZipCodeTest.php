<?php

namespace Tests\Unit\Models;

use App\Models\ForbiddenZipCode;


class ForbiddenZipCodeTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(ForbiddenZipCode::tableName(), [
            'id',
            'zip_code',
            'created_at',
            'updated_at',
        ]);
    }
}

<?php

namespace Tests\Unit\Models\Technician;

use App\Models\Technician;
use Tests\Unit\Models\ModelTestCase;

class TechnicianTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Technician::tableName(), [
            'id',
            'name',
            'code',
            'phone',
            'image',
            'years_of_experience',
            'show_in_app',
            'created_at',
            'updated_at',
        ]);
    }
}

<?php

namespace Tests\Unit\Models;

use App\Models\Term;

class TermTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Term::tableName(), [
            'id',
            'title',
            'body',
            'link',
            'required_at',
            'created_at',
            'updated_at',
        ]);
    }
}

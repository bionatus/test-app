<?php

namespace Tests\Unit\Models;

use App\Models\UserTaggable;

class UserTaggableTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(UserTaggable::tableName(), [
            'id',
            'user_id',
            'taggable_type',
            'taggable_id',
        ]);
    }
}

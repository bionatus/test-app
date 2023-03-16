<?php

namespace Tests\Unit\Models;

use App\Models\Tag;

class TagTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(Tag::tableName(), [
            'id',
            'post_id',
            'taggable_id',
            'taggable_type',
        ]);
    }
}

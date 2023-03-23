<?php

namespace Tests\Unit\Models;

use App\Models\CommentUser;

class CommentUserTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(CommentUser::tableName(), [
            'id',
            'user_id',
            'comment_id',
            'created_at',
            'updated_at',
        ]);
    }
}

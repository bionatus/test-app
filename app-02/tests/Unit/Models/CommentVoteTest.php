<?php

namespace Tests\Unit\Models;

use App\Models\CommentVote;

class CommentVoteTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(CommentVote::tableName(), [
            'id',
            'user_id',
            'comment_id',
            'created_at',
            'updated_at',
        ]);
    }
}

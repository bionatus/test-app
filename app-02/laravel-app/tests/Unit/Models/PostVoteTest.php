<?php

namespace Tests\Unit\Models;

use App\Models\PostVote;

class PostVoteTest extends ModelTestCase
{
    /** @test */
    public function it_has_expected_columns()
    {
        $this->assertHasExpectedColumns(PostVote::tableName(), [
            'id',
            'user_id',
            'post_id',
            'created_at',
            'updated_at',
        ]);
    }
}

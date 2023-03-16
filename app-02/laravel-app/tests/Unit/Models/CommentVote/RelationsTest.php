<?php

namespace Tests\Unit\Models\CommentVote;

use App\Models\Comment;
use App\Models\CommentVote;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property CommentVote $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = CommentVote::factory()->create();
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $related = $this->instance->user()->first();

        $this->assertInstanceOf(User::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_comment()
    {
        $related = $this->instance->comment()->first();

        $this->assertInstanceOf(Comment::class, $related);
    }
}

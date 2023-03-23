<?php

namespace Tests\Unit\Models\CommentUser;

use App\Models\Comment;
use App\Models\CommentUser;
use App\Models\User;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property CommentUser $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = CommentUser::factory()->create();
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

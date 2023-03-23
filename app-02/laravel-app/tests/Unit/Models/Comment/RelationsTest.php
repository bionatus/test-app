<?php

namespace Tests\Unit\Models\Comment;

use App\Models\Comment;
use App\Models\CommentUser;
use App\Models\CommentVote;
use App\Models\Post;
use App\Models\User;
use Auth;
use Tests\Unit\Models\RelationsTestCase;

/**
 * @property Comment $instance
 */
class RelationsTest extends RelationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Comment::factory()->create();
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $related = $this->instance->user()->first();

        $this->assertInstanceOf(User::class, $related);
    }

    /** @test */
    public function it_belongs_to_a_post()
    {
        $related = $this->instance->post()->first();

        $this->assertInstanceOf(Post::class, $related);
    }

    /** @test */
    public function it_has_votes()
    {
        CommentVote::factory()->usingComment($this->instance)->count(self::COUNT)->create();

        $related = $this->instance->votes()->get();

        $this->assertCorrectRelation($related, CommentVote::class);
    }

    /** @test */
    public function it_has_latest_five_votes()
    {
        $commentVotes = CommentVote::factory()
            ->usingComment($this->instance)
            ->withDecrementingCreationDate()
            ->count(self::COUNT)
            ->create();

        $related = $this->instance->latestFiveVotes()->get();

        $this->assertCorrectRelation($related, CommentVote::class, null, 5);

        $latestFiveVotes = $commentVotes->sortByDesc('created_at')->take(5);

        $this->assertEquals($latestFiveVotes->pluck(CommentVote::keyName()), $related->pluck(CommentVote::keyName()));
    }

    /** @test */
    public function it_has_an_auth_user_vote()
    {
        $commentVote = CommentVote::factory()->usingComment($this->instance)->create();
        Auth::shouldReceive('user')->andReturn($commentVote->user);
        $related = $this->instance->authUserVote()->first();

        $this->assertInstanceOf(CommentVote::class, $related);
        $this->assertEquals($commentVote->getKey(), $related->getKey());
    }

    /** @test */
    public function it_has_tagged_users()
    {
        CommentUser::factory()->usingComment($this->instance)->count(10)->create();

        $related = $this->instance->taggedUsers()->get();

        $this->assertCorrectRelation($related, User::class);
    }

    /** @test */
    public function it_has_comment_users()
    {
        CommentUser::factory()->usingComment($this->instance)->count(10)->create();

        $related = $this->instance->commentUsers()->get();

        $this->assertCorrectRelation($related, CommentUser::class);
    }
}

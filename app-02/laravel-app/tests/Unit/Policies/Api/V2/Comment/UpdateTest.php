<?php

namespace Tests\Unit\Policies\Api\V2\Comment;

use App\Models\Comment;
use App\Models\User;
use App\Policies\Api\V2\CommentPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_disallows_anyone_to_update_a_solution_comment()
    {
        $user      = User::factory()->create();
        $moderator = User::factory()->moderator()->create();
        $comment   = Comment::factory()->solution()->create();

        $policy = new CommentPolicy();

        $this->assertFalse($policy->update($user, $comment));
        $this->assertFalse($policy->update($moderator, $comment));
        $this->assertFalse($policy->update($comment->user, $comment));
    }

    /** @test */
    public function it_allows_a_moderator_to_update_a_not_solution_comment()
    {
        $moderator = User::factory()->moderator()->create();
        $comment   = Comment::factory()->create();

        $policy = new CommentPolicy();

        $this->assertTrue($policy->update($moderator, $comment));
    }

    /** @test */
    public function it_allows_the_owner_to_update_a_not_solution_comment()
    {
        $comment = Comment::factory()->create();

        $policy = new CommentPolicy();

        $this->assertTrue($policy->update($comment->user, $comment));
    }

    /** @test */
    public function it_disallow_another_user_to_update_it()
    {
        $comment = Comment::factory()->create();

        $policy = new CommentPolicy();

        $this->assertFalse($policy->delete(new User(), $comment));
    }
}

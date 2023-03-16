<?php

namespace Tests\Unit\Policies\Api\V2\Comment;

use App\Models\Comment;
use App\Models\User;
use App\Policies\Api\V2\CommentPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_a_moderator_to_delete_a_solution_comment()
    {
        $moderator = User::factory()->moderator()->create();
        $comment   = Comment::factory()->solution()->create();

        $policy = new CommentPolicy();

        $this->assertTrue($policy->delete($moderator, $comment));
    }

    /** @test */
    public function it_allows_a_moderator_to_delete_a_not_solution_comment()
    {
        $moderator = User::factory()->moderator()->create();
        $comment   = Comment::factory()->create();

        $policy = new CommentPolicy();

        $this->assertTrue($policy->delete($moderator, $comment));
    }

    /** @test */
    public function it_disallows_the_owner_to_delete_a_solution_comment()
    {
        $comment = Comment::factory()->solution()->create();

        $policy = new CommentPolicy();

        $this->assertFalse($policy->delete($comment->user, $comment));
    }

    /** @test */
    public function it_allows_the_owner_to_delete_a_not_solution_comment()
    {
        $comment = Comment::factory()->create();

        $policy = new CommentPolicy();

        $this->assertTrue($policy->delete($comment->user, $comment));
    }

    /** @test */
    public function it_disallow_another_user_to_delete_it()
    {
        $comment = Comment::factory()->create();

        $policy = new CommentPolicy();

        $this->assertFalse($policy->delete(new User(), $comment));
    }
}

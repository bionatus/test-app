<?php

namespace Tests\Unit\Policies\Api\V2\Post;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Policies\Api\V2\PostPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_a_moderator_to_delete_a_solved_post()
    {
        $moderator = User::factory()->moderator()->create();
        $post      = Comment::factory()->solution()->create()->post;

        $policy = new PostPolicy();

        $this->assertTrue($policy->delete($moderator, $post));
    }

    /** @test */
    public function it_allows_a_moderator_to_delete_a_not_solved_post()
    {
        $moderator = User::factory()->moderator()->create();
        $post      = Post::factory()->create();

        $policy = new PostPolicy();

        $this->assertTrue($policy->delete($moderator, $post));
    }

    /** @test */
    public function it_disallows_the_owner_to_delete_a_solved_post()
    {
        $post = Comment::factory()->solution()->create()->post;

        $policy = new PostPolicy();

        $this->assertFalse($policy->delete($post->user, $post));
    }

    /** @test */
    public function it_allows_the_owner_to_delete_a_not_solved_post()
    {
        $post = Post::factory()->create();

        $policy = new PostPolicy();

        $this->assertTrue($policy->delete($post->user, $post));
    }

    /** @test */
    public function it_disallow_another_user_to_delete_it()
    {
        $post = Post::factory()->create();

        $policy = new PostPolicy();

        $this->assertFalse($policy->delete(new User(), $post));
    }
}

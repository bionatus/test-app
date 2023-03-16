<?php

namespace Tests\Unit\Policies\Api\V2\Post;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Policies\Api\V2\PostPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_disallows_anyone_to_update_a_solved_post()
    {
        $user      = User::factory()->create();
        $moderator = User::factory()->moderator()->create();
        $post      = Comment::factory()->solution()->create()->post;

        $policy = new PostPolicy();

        $this->assertFalse($policy->update($user, $post));
        $this->assertFalse($policy->update($moderator, $post));
        $this->assertFalse($policy->update($post->user, $post));
    }

    /** @test */
    public function it_allows_a_moderator_to_update_a_not_solved_post()
    {
        $moderator = User::factory()->moderator()->create();
        $post      = Post::factory()->create();

        $policy = new PostPolicy();

        $this->assertTrue($policy->update($moderator, $post));
    }

    /** @test */
    public function it_allows_the_owner_to_update_a_not_solved_post()
    {
        $post = Post::factory()->create();

        $policy = new PostPolicy();

        $this->assertTrue($policy->update($post->user, $post));
    }
}

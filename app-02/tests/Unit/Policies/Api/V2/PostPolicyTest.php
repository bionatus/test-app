<?php

namespace Tests\Unit\Policies\Api\V2;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Policies\Api\V2\PostPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostPolicyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_owner_to_update_it()
    {
        $post = Post::factory()->create();

        $policy = new PostPolicy();

        $this->assertTrue($policy->update($post->user, $post));
    }

    /** @test */
    public function it_allows_moderator_to_update_it()
    {
        $post = Post::factory()->create();

        $policy = new PostPolicy();

        $this->assertTrue($policy->update($post->user, $post));
    }

    /** @test */
    public function it_disallows_to_update_it_if_it_is_solved()
    {
        $post = Post::factory()->create();
        Comment::factory()->usingPost($post)->solution()->create();

        $policy = new PostPolicy();

        $this->assertFalse($policy->update($post->user, $post));
    }

    /** @test */
    public function it_allows_to_delete_it_if_it_is_not_pinned()
    {
        $post = Post::factory()->pinned()->create();

        $policy = new PostPolicy();

        $this->assertFalse($policy->delete($post->user, $post));
    }

    /** @test */
    public function it_disallows_to_delete_it_if_it_is_pinned()
    {
        $post = Post::factory()->pinned()->create();

        $policy = new PostPolicy();

        $this->assertFalse($policy->delete($post->user, $post));
    }

    /** @test */
    public function it_disallows_a_non_moderator_user_to_delete_it_if_it_is_solved()
    {
        $post = Post::factory()->create();
        Comment::factory()->usingPost($post)->solution()->create();

        $policy = new PostPolicy();

        $this->assertFalse($policy->delete($post->user, $post));
    }

    /** @test */
    public function it_allows_moderator_to_delete_it()
    {
        $user = User::factory()->create(['email' => 'acurry@bionatusllc.com']);
        $post = Post::factory()->usingUser($user)->create();

        $policy = new PostPolicy();

        $this->assertTrue($policy->delete($post->user, $post));
    }

    /** @test */
    public function it_allows_owner_to_delete_it_if_it_is_not_solved()
    {
        $post = Post::factory()->create();

        $policy = new PostPolicy();

        $this->assertTrue($policy->delete($post->user, $post));
    }

    /** @test */
    public function it_disallows_owner_to_delete_it_if_it_is_solved()
    {
        $post = Post::factory()->create();
        Comment::factory()->usingPost($post)->solution()->create();

        $policy = new PostPolicy();

        $this->assertFalse($policy->delete($post->user, $post));
    }

    /** @test */
    public function it_allows_owner_to_solve_it()
    {
        $post = Post::factory()->create();

        $policy = new PostPolicy();

        $this->assertTrue($policy->solve($post->user, $post));
    }

    /** @test */
    public function it_disallow_another_user_to_solve_it()
    {
        $post = Post::factory()->create();

        $policy = new PostPolicy();

        $this->assertFalse($policy->solve(new User(), $post));
    }

    /** @test */
    public function it_allows_owner_to_un_solve_it()
    {
        $post = Post::factory()->create();

        $policy = new PostPolicy();

        $this->assertTrue($policy->unSolve($post->user, $post));
    }

    /** @test */
    public function it_disallow_another_user_to_un_solve_it()
    {
        $post = Post::factory()->create();

        $policy = new PostPolicy();

        $this->assertFalse($policy->unSolve(new User(), $post));
    }
}

<?php

namespace Tests\Unit\Models\User\Scopes;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Models\User\Scopes\ByPostOrComments;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByPostOrCommentsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_post_or_comments()
    {
        $user = User::factory()->create();
        User::factory()->count(3)->create();
        Post::factory()->count(5)->create();
        Comment::factory()->count(6)->create();
        
        $post = Post::factory()->usingUser($user)->create();
        Comment::factory()->usingPost($post)->count(4)->create();

        $filtered = User::scoped(new ByPostOrComments($post))->count();

        $this->assertSame(5, $filtered);
    }
}

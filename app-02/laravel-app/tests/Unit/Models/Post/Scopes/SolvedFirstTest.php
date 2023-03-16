<?php

namespace Tests\Unit\Models\Post\Scopes;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Post\Scopes\SolvedFirst;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SolvedFirstTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_orders_by_solved_first()
    {
        Post::factory()->count(5)->create();
        $post = Post::factory()->create();
        Comment::factory()->usingPost($post)->solution()->create();
        Post::factory()->count(5)->create();
        Post::scoped(new SolvedFirst())->get();

        $this->assertEquals($post->id, Post::scoped(new Post\Scopes\SolvedFirst())->get()->first()->id);
    }
}

<?php

namespace Tests\Unit\Models\Comment\Scopes;

use App\Models\Comment;
use App\Models\Comment\Scopes\SolutionsFirst;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SolutionsFirstTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_prioritizes_solution_comments()
    {
        $post = Post::factory()->create();
        Comment::factory()->usingPost($post)->count(3)->create();
        $solution = Comment::factory()->usingPost($post)->solution()->create();
        Comment::factory()->usingPost($post)->count(3)->create();

        $this->assertEquals($solution->getKey(),
            $post->comments()->scoped(new SolutionsFirst())->get()->first()->getKey());
    }
}

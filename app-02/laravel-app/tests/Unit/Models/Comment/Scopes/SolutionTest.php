<?php

namespace Tests\Unit\Models\Comment\Scopes;

use App\Models\Comment;
use App\Models\Comment\Scopes\Solution;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SolutionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_solution_comments()
    {
        $post = Post::factory()->create();
        Comment::factory()->usingPost($post)->count(3)->create();
        $solution = Comment::factory()->usingPost($post)->solution()->create();
        Comment::factory()->usingPost($post)->count(3)->create();

        $filteredComments = $post->comments()->scoped(new Solution())->get();
        $solutionComment  = $filteredComments->first();

        $this->assertEquals($solution->getKey(), $solutionComment->id);
        $this->assertCount(1, $filteredComments);
    }
}

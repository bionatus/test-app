<?php

namespace Tests\Unit\Models\Comment\Scopes;

use App\Models\Comment;
use App\Models\Comment\Scopes\Oldest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OldestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_orders_by_oldest_creation_and_id()
    {
        $comments        = Comment::factory()->count(3)->create();
        $orderedComments = Comment::scoped(new Oldest())->get();

        $orderedComments->each(function(Comment $comment) use ($comments) {
            $this->assertSame($comments->shift()->getKey(), $comment->getKey());
        });
    }
}

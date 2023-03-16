<?php

namespace Tests\Unit\Observers;

use App\Models\Comment;
use App\Observers\CommentObserver;
use Tests\TestCase;

class CommentObserverTest extends TestCase
{
    /** @test */
    public function it_fills_uuid_when_creating()
    {
        $comment = Comment::factory()->make(['uuid' => null, 'user_id' => 1, 'post_id' => 1]);

        $observer = new CommentObserver();

        $observer->creating($comment);

        $this->assertNotNull($comment->uuid);
    }

    /** @test */
    public function it_updates_content_updated_at_when_changing_the_message()
    {
        $comment  = Comment::factory()->make(['user_id' => 1, 'post_id' => 1]);
        $observer = new CommentObserver();

        $comment->message = 'Updated message';

        $this->assertNull($comment->content_updated_at);
        $observer->updating($comment);

        $this->assertNotNull($comment->content_updated_at);
    }
}

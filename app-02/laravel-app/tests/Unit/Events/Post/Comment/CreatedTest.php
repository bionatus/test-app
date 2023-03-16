<?php

namespace Tests\Unit\Events\Post\Comment;

use App\Events\Post\Comment\Created;
use App\Listeners\SendCommentPostRepliedNotification;
use App\Listeners\SendPostRepliedNotification;
use Tests\TestCase;

class CreatedTest extends TestCase
{
    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(Created::class, [
            SendPostRepliedNotification::class,
            SendCommentPostRepliedNotification::class,
        ]);
    }
}

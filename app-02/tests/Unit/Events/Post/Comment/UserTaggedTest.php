<?php

namespace Tests\Unit\Events\Post\Comment;

use App\Events\Post\Comment\UserTagged;
use App\Listeners\SendCommentUsersTaggedNotification;
use Tests\TestCase;

class UserTaggedTest extends TestCase
{
    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(UserTagged::class, [
            SendCommentUsersTaggedNotification::class,
        ]);
    }
}

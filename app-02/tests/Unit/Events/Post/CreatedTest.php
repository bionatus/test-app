<?php

namespace Tests\Unit\Events\Post;

use App\Events\Post\Created;
use App\Listeners\SendPostCreatedNotification;
use Tests\TestCase;

class CreatedTest extends TestCase
{
    /** @test */
    public function it_has_a_send_post_created_notification_listener()
    {
        $this->assertEventHasListeners(Created::class, [
            SendPostCreatedNotification::class,
        ]);
    }
}

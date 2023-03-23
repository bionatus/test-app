<?php

namespace Tests\Unit\Events\User;

use App\Events\User\NewMessage;
use App\Listeners\User\SendNewMessagePubnubNotification;
use Tests\TestCase;

class NewMessageTest extends TestCase
{
    /** @test */
    public function it_has_a_send_new_message_notification_listener()
    {
        $this->assertEventHasListeners(NewMessage::class, [
            SendNewMessagePubnubNotification::class,
        ]);
    }
}

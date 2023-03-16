<?php

namespace Tests\Unit\Events\Supplier;

use App\Events\Supplier\NewMessage;
use App\Listeners\Supplier\SendPubnubNewMessageNotification;
use Tests\TestCase;

class NewMessageTest extends TestCase
{
    /** @test */
    public function it_has_a_send_new_message_notification_listener()
    {
        $this->assertEventHasListeners(NewMessage::class, [
            SendPubnubNewMessageNotification::class,
        ]);
    }
}

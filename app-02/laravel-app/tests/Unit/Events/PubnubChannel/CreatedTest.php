<?php

namespace Tests\Unit\Events\PubnubChannel;

use App\Events\PubnubChannel\Created;
use App\Listeners\User\SendInitialPubnubMessage;
use App\Models\PubnubChannel;
use Tests\TestCase;

class CreatedTest extends TestCase
{
    /** @test */
    public function it_has_listeners()
    {
        $this->assertEventHasListeners(Created::class, [
            SendInitialPubnubMessage::class,
        ]);
    }

    /** @test */
    public function it_returns_its_channel()
    {
        $pubnubChannel = new PubnubChannel();

        $event = new Created($pubnubChannel);

        $this->assertSame($pubnubChannel, $event->pubnubChannel());
    }
}

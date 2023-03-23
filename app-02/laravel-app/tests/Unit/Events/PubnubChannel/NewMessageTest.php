<?php

namespace Tests\Unit\Events\PubnubChannel;

use App\Events\PubnubChannel\NewMessageEventInterface;
use App\Events\PubnubChannel\NewMessage;
use App\Models\Supplier;
use App\Models\User;
use ReflectionClass;
use Tests\TestCase;

class NewMessageTest extends TestCase
{
    /** @test */
    public function it_implements_interface()
    {
        $reflection = new ReflectionClass(NewMessage::class);

        $this->assertTrue($reflection->implementsInterface(NewMessageEventInterface::class));
    }

    /** @test */
    public function it_returns_its_supplier()
    {
        $supplier = new Supplier();
        $user     = new User();
        $message  = 'Test Message';

        $event = $this->newMessageStub($supplier, $user, $message);

        $this->assertSame($supplier, $event->supplier());
    }

    /** @test */
    public function it_returns_its_user()
    {
        $supplier = new Supplier();
        $user     = new User();
        $message  = 'Test Message';

        $event = $this->newMessageStub($supplier, $user, $message);

        $this->assertSame($user, $event->user());
    }

    /** @test */
    public function it_returns_its_message()
    {
        $supplier = new Supplier();
        $user     = new User();
        $message  = 'Test Message';

        $event = $this->newMessageStub($supplier, $user, $message);

        $this->assertSame($message, $event->message());
    }

    private function newMessageStub($supplier, $user, $message): NewMessage
    {
        return new class($supplier, $user, $message) extends NewMessage {
        };
    }
}

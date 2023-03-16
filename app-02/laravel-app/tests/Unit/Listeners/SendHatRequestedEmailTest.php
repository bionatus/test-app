<?php

namespace Tests\Unit\Listeners;

use App\Events\User\HatRequested;
use App\Listeners\SendHatRequestedEmail;
use App\Mail\HatRequestedEmail;
use App\Models\User;
use Config;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Mail;
use ReflectionClass;
use Tests\TestCase;

class SendHatRequestedEmailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(SendHatRequestedEmail::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_uses_database_connection()
    {
        $listener = new SendHatRequestedEmail();

        $this->assertEquals('database', $listener->connection);
    }

    /** @test
     * @throws Exception
     */
    public function it_dispatch_an_email_to_support()
    {
        $supportHatEmails = ['jon@doe.com', 'jane@doe.com'];
        Config::set('mail.support.hat', $supportHatEmails);

        Mail::fake();

        $user = User::factory()->create();

        $event    = new HatRequested($user);
        $listener = new SendHatRequestedEmail();
        $listener->handle($event);

        Mail::assertSent(HatRequestedEmail::class, function(HatRequestedEmail $mailable) use ($supportHatEmails) {
            $receivers = Collection::make($mailable->to)->plucK('address');
            $this->assertEqualsCanonicalizing($receivers->toArray(), $supportHatEmails);

            return true;
        });
    }
}

<?php

namespace Tests\Unit\Jobs;

use App\Jobs\PhoneAuthenticationCall;
use App\Models\Phone;
use App\Services\Twilio\RestClient;
use Config;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use ReflectionClass;
use Tests\TestCase;
use Twilio\Exceptions\ConfigurationException;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Api\V2010\Account\CallInstance;
use Twilio\Rest\Api\V2010\Account\CallList;

class PhoneAuthenticationCallTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_queued()
    {
        $reflection = new ReflectionClass(PhoneAuthenticationCall::class);

        $this->assertTrue($reflection->implementsInterface(ShouldQueue::class));
    }

    /** @test */
    public function it_is_sent_using_the_sync_queue()
    {
        $job = new PhoneAuthenticationCall(new Phone());

        $this->assertSame('sync', $job->connection);
    }

    /** @test
     *
     * @throws ConfigurationException
     * @throws TwilioException
     */
    public function it_makes_a_phone_call()
    {
        Config::set('twilio.account_sid', 'sid');
        Config::set('twilio.auth_token', 'token');
        Config::set('twilio.numbers.auth', '5555555555');
        $callInstance = Mockery::mock(CallInstance::class);

        $client = new class($callInstance) extends RestClient {
            public CallList $calls;

            public function __construct(CallInstance $callInstance)
            {
                parent::__construct();
                $callList = Mockery::mock(CallList::class);
                $callList->shouldReceive('create')->withAnyArgs()->once()->andReturn($callInstance);
                $this->calls = $callList;
            }
        };

        $phone = Mockery::mock(Phone::class);
        $phone->shouldReceive('fullNumber')->withNoArgs()->once()->andReturn('555222810');

        $job      = new PhoneAuthenticationCall($phone);
        $response = $job->handle($client);

        $this->assertSame($callInstance, $response);
    }
}

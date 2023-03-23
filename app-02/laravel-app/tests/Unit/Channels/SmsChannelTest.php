<?php

namespace Tests\Unit\Channels;

use App;
use App\Channels\SmsChannel;
use App\Models\AuthenticationCode;
use App\Models\Phone;
use App\Notifications\Phone\SmsRequestedNotification;
use App\Services\Twilio\RestClient;
use Config;
use Mockery;
use Tests\TestCase;
use Twilio\Exceptions\ConfigurationException;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Api\V2010\Account\MessageInstance;
use Twilio\Rest\Api\V2010\Account\MessageList;

class SmsChannelTest extends TestCase
{
    /** @test
     *
     * @throws ConfigurationException
     * @throws TwilioException
     */
    public function it_does_not_send_an_sms_if_phone_number_is_empty()
    {
        Config::set('twilio.account_sid', 'sid');
        Config::set('twilio.auth_token', 'token');

        $notification = new SmsRequestedNotification(new AuthenticationCode());
        $phone        = Mockery::mock(Phone::class);
        $phone->shouldReceive('routeNotificationFor')->withArgs(['sms', $notification])->once()->andReturn('');

        $channel  = App::make(SmsChannel::class);
        $response = $channel->send($phone, $notification);

        $this->assertSame([], $response);
    }

    /** @test
     *
     * @throws ConfigurationException
     * @throws TwilioException
     */
    public function it_sends_an_sms_message()
    {
        Config::set('twilio.account_sid', 'sid');
        Config::set('twilio.auth_token', 'token');
        $messageInstance = Mockery::mock(MessageInstance::class);

        $client = new class($messageInstance) extends RestClient {
            public MessageList $messages;

            public function __construct(MessageInstance $messageInstance)
            {
                parent::__construct();
                $messagesList = Mockery::mock(MessageList::class);
                $messagesList->shouldReceive('create')->withAnyArgs()->once()->andReturn($messageInstance);
                $this->messages = $messagesList;
            }
        };

        $notification = Mockery::mock(SmsRequestedNotification::class);
        $notification->shouldReceive('toSms')->withNoArgs()->once()->andReturn('A message');
        $phone = Mockery::mock(Phone::class);
        $phone->shouldReceive('routeNotificationFor')->withArgs(['sms', $notification])->once()->andReturn('555222810');

        $channel  = new SmsChannel($client);
        $response = $channel->send($phone, $notification);

        $this->assertSame([$messageInstance], $response);
    }
}

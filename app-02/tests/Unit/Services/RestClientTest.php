<?php

namespace Tests\Unit\Services;

use App\Services\Twilio\RestClient;
use Config;
use ReflectionClass;
use Tests\TestCase;
use Twilio\Exceptions\ConfigurationException;
use Twilio\Rest\Client;

class RestClientTest extends TestCase
{
    /** @test */
    public function it_extends_twilio_rest_client()
    {
        $reflection = new ReflectionClass(RestClient::class);
        $parent     = $reflection->getParentClass()->getName();

        $this->assertSame(Client::class, $parent);
    }

    /** @test */
    public function it_throws_exception_when_no_credentials_are_provided()
    {
        Config::set('twilio.account_sid');
        Config::set('twilio.auth_token');

        $this->expectException(ConfigurationException::class);
        new RestClient();
    }

    /** @test */
    public function it_is_authenticated_with_config_values()
    {
        Config::set('twilio.account_sid', $accountSid = 'sid');
        Config::set('twilio.auth_token', $authToken = 'auth_token');

        $client = new RestClient();

        $this->assertSame($accountSid, $client->getUsername());
        $this->assertSame($accountSid, $client->getAccountSid());
        $this->assertSame($authToken, $client->getPassword());
    }
}

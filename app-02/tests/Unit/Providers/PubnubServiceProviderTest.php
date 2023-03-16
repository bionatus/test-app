<?php

namespace Tests\Unit\Providers;

use App\Providers\PubnubServiceProvider;
use Config;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\ServiceProvider;
use Mockery;
use PubNub\PubNub;
use Tests\TestCase;

class PubnubServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    private PubnubServiceProvider $pubnubServiceProvider;

    protected function setUp(): void
    {
        $application                 = Mockery::mock(Application::class);
        $this->pubnubServiceProvider = new PubnubServiceProvider($application);

        parent::setUp();
    }

    /** @test */
    public function it_can_be_constructed()
    {
        $this->assertInstanceOf(ServiceProvider::class, $this->pubnubServiceProvider);
    }

    /** @test */
    public function it_turns_pubnub_class_into_a_singleton()
    {
        Config::set('pubnub.publish_key', 'publish key');
        Config::set('pubnub.secret_key', 'secret key');
        Config::set('pubnub.subscribe_key', 'subscribe key');
        Config::set('pubnub.uuid', 'uuid');

        $actual   = $this->app->make(PubNub::class);
        $expected = $this->app->make(PubNub::class);

        $this->assertTrue($this->app->bound(PubNub::class));
        $this->assertSame($actual, $expected);
    }

    /** @test */
    public function it_uses_the_sent_uuid_over_the_uuid_from_the_config()
    {
        Config::set('pubnub.publish_key', 'publish key');
        Config::set('pubnub.secret_key', 'secret key');
        Config::set('pubnub.subscribe_key', 'subscribe key');
        Config::set('pubnub.uuid', 'uuid');

        $pubnub   = $this->app->make(PubNub::class, ['uuid' => $uuid = 'new uuid']);

        $this->assertSame($uuid, $pubnub->getConfiguration()->getUuid());
    }
}

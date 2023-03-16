<?php

namespace Tests\Unit\Rules\Call;

use App\Models\Call;
use App\Rules\Call\Exists;
use App\Rules\Call\NotExpired;
use Carbon\Carbon;
use Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class NotExpiredTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_a_message()
    {
        $rule = new NotExpired(new Exists(''));

        $this->assertSame('The :attribute is expired', $rule->message());
    }

    /** @test */
    public function it_fails_if_there_is_no_call()
    {
        $rule = new NotExpired(new Exists(''));

        $this->assertFalse($rule->passes('attribute', 'invalid'));
    }

    /** @test */
    public function it_fails_if_call_has_been_waiting_more_than_the_max_allowed_in_config()
    {
        $maxTechWaitingTime = (int) Config::get('communications.calls.max_user_waiting_time');

        $call = Call::factory()->create(['created_at' => Carbon::now()->subSeconds($maxTechWaitingTime + 1)]);

        $mock = Mockery::mock(Exists::class);
        $mock->shouldReceive('call')->withNoArgs()->once()->andReturn($call);
        $rule = new NotExpired($mock);
        $this->assertFalse($rule->passes('attribute', $call->communication->provider_id));
    }

    /** @test */
    public function it_passes()
    {
        $call = Call::factory()->create();

        $mock = Mockery::mock(Exists::class);
        $mock->shouldReceive('call')->withNoArgs()->once()->andReturn($call);
        $rule = new NotExpired($mock);
        $this->assertTrue($rule->passes('attribute', $call->communication->provider_id));
    }
}

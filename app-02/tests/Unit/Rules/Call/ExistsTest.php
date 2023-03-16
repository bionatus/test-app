<?php

namespace Tests\Unit\Rules\Call;

use App\Models\Call;
use App\Models\Communication;
use App\Rules\Call\Exists;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lang;
use Tests\TestCase;

class ExistsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_a_message()
    {
        $rule = new Exists('provider');

        $this->assertSame(Lang::get('validation.exists'), $rule->message());
    }

    /** @test */
    public function it_fails_if_there_is_no_communication()
    {
        $rule = new Exists('provider');
        $this->assertFalse($rule->passes('attribute', 'invalid'));
    }

    /** @test */
    public function it_fails_if_communication_is_not_a_call()
    {
        $provider              = Communication::PROVIDER_TWILIO;
        $chatCommunication     = Communication::factory()->chat()->create(['provider' => $provider]);
        $noDBCallCommunication = Communication::factory()->call()->create(['provider' => $provider]);

        $rule = new Exists($provider);
        $this->assertFalse($rule->passes('attribute', $chatCommunication->provider_id));
        $this->assertFalse($rule->passes('attribute', $noDBCallCommunication->provider_id));
    }

    /** @test */
    public function it_returns_an_stub_call_if_fails()
    {
        $rule = new Exists('provider');

        $this->assertFalse($rule->call()->exists);
    }

    /** @test */
    public function it_passes()
    {
        $call = Call::factory()->create();

        $rule = new Exists($call->communication->provider);
        $this->assertTrue($rule->passes('attribute', $call->communication->provider_id));
    }

    /** @test */
    public function it_returns_a_real_call_if_passes()
    {
        $call = Call::factory()->create();

        $rule = new Exists($call->communication->provider);
        $rule->passes('attribute', $call->communication->provider_id);
        $this->assertSame($call->getKey(), $rule->call()->getKey());
    }
}

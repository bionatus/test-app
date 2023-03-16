<?php

namespace Tests\Unit\Models\Call\Scopes;

use App\Models\Call;
use App\Models\Call\Scopes\ByProvider;
use App\Models\Communication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByProviderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_provider_on_numeric_provider_id()
    {
        $provider   = Communication::PROVIDER_TWILIO;
        $providerId = 123;

        $communication = Communication::factory()->call()->create([
            'provider'    => $provider,
            'provider_id' => $providerId,
        ]);

        Call::factory()->usingCommunication($communication)->create();
        Call::factory()->create();

        $communications = Call::scoped(new ByProvider($provider, $providerId))->get();
        $this->assertCount(1, $communications);
        $communication = Call::scoped(new ByProvider($provider, 456))->first();
        $this->assertNull($communication);
    }
}

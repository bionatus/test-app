<?php

namespace Tests\Unit\Models\Communication\Scopes;

use App\Models\Communication;
use App\Models\Communication\Scopes\ByProvider;
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

        Communication::factory()->create([
            'provider'    => $provider,
            'provider_id' => $providerId,
        ]);
        Communication::factory()->create([
            'provider'    => $provider,
            'provider_id' => '321',
        ]);

        $communications = Communication::scoped(new ByProvider($provider, $providerId))->get();
        $this->assertCount(1, $communications);
        $communication = Communication::scoped(new ByProvider($provider, 456))->first();
        $this->assertNull($communication);
    }
}

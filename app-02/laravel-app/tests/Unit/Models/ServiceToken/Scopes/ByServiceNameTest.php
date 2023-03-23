<?php

namespace Tests\Unit\Models\ServiceToken\Scopes;

use App\Models\ServiceToken;
use App\Models\ServiceToken\Scopes\ByServiceName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ByServiceNameTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_service_token_by_service_name()
    {
        $serviceName      = ServiceToken::XOXO;
        $xoxoRefreshToken = ServiceToken::factory()->create([
            'service_name' => $serviceName,
            'token_name'   => ServiceToken::REFRESH_TOKEN,
        ]);
        $xoxoAccessToken  = ServiceToken::factory()->create([
            'service_name' => $serviceName,
            'token_name'   => ServiceToken::ACCESS_TOKEN,
        ]);
        $expected         = Collection::make([$xoxoAccessToken, $xoxoRefreshToken]);
        ServiceToken::factory()->create(['service_name' => 'another service']);

        $filtered = ServiceToken::scoped(new ByServiceName(ServiceToken::XOXO))->get();

        $this->assertCount(2, $filtered);
        $filtered->each(function(ServiceToken $serviceToken) use ($expected) {
            $this->assertSame($expected->shift()->getKey(), $serviceToken->getKey());
        });
    }
}

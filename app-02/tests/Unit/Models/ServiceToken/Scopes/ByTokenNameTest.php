<?php

namespace Tests\Unit\Models\ServiceToken\Scopes;

use App\Models\ServiceToken;
use App\Models\ServiceToken\Scopes\ByTokenName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ByTokenNameTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_service_token_by_token_name()
    {
        ServiceToken::factory()->create(['token_name' => ServiceToken::ACCESS_TOKEN]);
        $refreshToken        = ServiceToken::factory()->create();
        $anotherRefreshToken = ServiceToken::factory()->create(['service_name' => 'another service name']);
        $expected            = Collection::make([$refreshToken, $anotherRefreshToken]);

        $filtered = ServiceToken::scoped(new ByTokenName(ServiceToken::REFRESH_TOKEN))->get();

        $this->assertCount(2, $expected);
        $filtered->each(function(ServiceToken $serviceToken) use ($expected) {
            $this->assertSame($expected->shift()->getKey(), $serviceToken->getKey());
        });
    }
}

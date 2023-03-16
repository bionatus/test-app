<?php

namespace Tests\Unit\Observers;

use App\Models\XoxoRedemption;
use App\Observers\XoxoRedemptionObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XoxoRedemptionObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fills_uuid_when_creating()
    {
        $xoxoRedemption = XoxoRedemption::factory()->make(['uuid' => null]);

        $observer = new XoxoRedemptionObserver();

        $observer->creating($xoxoRedemption);

        $this->assertNotNull($xoxoRedemption->getRouteKey());
    }
}

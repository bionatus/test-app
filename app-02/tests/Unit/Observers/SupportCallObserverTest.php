<?php

namespace Tests\Unit\Observers;

use App\Models\SupportCall;
use App\Observers\SupportCallObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportCallObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fills_uuid_when_creating()
    {
        $supportCall = SupportCall::factory()->make(['uuid' => null]);

        $observer = new SupportCallObserver();

        $observer->creating($supportCall);

        $this->assertNotNull($supportCall->uuid);
    }
}

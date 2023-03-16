<?php

namespace Tests\Unit\Observers;

use App\Models\Communication;
use App\Observers\CommunicationObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunicationObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fills_uuid_when_creating()
    {
        $communication = Communication::factory()->make(['uuid' => null]);

        $observer = new CommunicationObserver();

        $observer->creating($communication);

        $this->assertNotNull($communication->uuid);
    }
}

<?php

namespace Tests\Unit\Observers;

use App\Models\Oem;
use App\Observers\OemObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OemObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fills_uuid_when_creating()
    {
        $oem = Oem::factory()->make(['uuid' => null]);

        $observer = new OemObserver();

        $observer->creating($oem);

        $this->assertNotNull($oem->uuid);
    }

    /** @test */
    public function it_does_not_change_uuid_when_not_empty()
    {
        $oem = Oem::factory()->make(['uuid' => $uuid = '123456']);

        $observer = new OemObserver();

        $observer->creating($oem);

        $this->assertSame($uuid, $oem->uuid);
    }
}

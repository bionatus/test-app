<?php

namespace Tests\Unit\Observers;

use App\Models\OemSearchCounter;
use App\Observers\OemSearchCounterObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OemSearchCounterObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fills_uuid_when_creating()
    {
        $item = OemSearchCounter::factory()->make(['uuid' => null]);

        $observer = new OemSearchCounterObserver();

        $observer->creating($item);

        $this->assertNotNull($item->uuid);
    }

    /** @test */
    public function it_does_not_change_uuid_when_not_empty()
    {
        $item = OemSearchCounter::factory()->make(['uuid' => $uuid = '123456']);

        $observer = new OemSearchCounterObserver();

        $observer->creating($item);

        $this->assertSame($uuid, $item->uuid);
    }
}

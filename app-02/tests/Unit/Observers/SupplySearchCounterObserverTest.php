<?php

namespace Tests\Unit\Observers;

use App\Models\SupplySearchCounter;
use App\Observers\SupplySearchCounterObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplySearchCounterObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fills_uuid_when_creating()
    {
        $item = SupplySearchCounter::factory()->make(['uuid' => null]);

        $observer = new SupplySearchCounterObserver();

        $observer->creating($item);

        $this->assertNotNull($item->uuid);
    }

    /** @test */
    public function it_does_not_change_uuid_when_not_empty()
    {
        $item = SupplySearchCounter::factory()->make(['uuid' => $uuid = '123456']);

        $observer = new SupplySearchCounterObserver();

        $observer->creating($item);

        $this->assertSame($uuid, $item->uuid);
    }
}

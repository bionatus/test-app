<?php

namespace Tests\Unit\Observers;

use App\Models\PartSearchCounter;
use App\Observers\PartSearchCounterObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartSearchCounterObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fills_uuid_when_creating()
    {
        $item = PartSearchCounter::factory()->make(['uuid' => null]);

        $observer = new PartSearchCounterObserver();

        $observer->creating($item);

        $this->assertNotNull($item->uuid);
    }

    /** @test */
    public function it_does_not_change_uuid_when_not_empty()
    {
        $item = PartSearchCounter::factory()->make(['uuid' => $uuid = '123456']);

        $observer = new PartSearchCounterObserver();

        $observer->creating($item);

        $this->assertSame($uuid, $item->uuid);
    }
}

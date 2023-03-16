<?php

namespace Tests\Unit\Observers;

use App;
use App\Models\Staff;
use App\Observers\StaffObserver;
use App\Services\Hubspot\Hubspot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class StaffObserverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fills_uuid_when_creating()
    {
        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('upsertCompany')->withAnyArgs()->once()->andReturnNull();
        App::bind(Hubspot::class, fn() => $hubspot);

        $staff = Staff::factory()->make(['uuid' => null]);

        $observer = new StaffObserver();

        $observer->creating($staff);

        $this->assertNotNull($staff->uuid);
    }
}

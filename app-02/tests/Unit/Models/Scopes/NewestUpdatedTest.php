<?php

namespace Tests\Unit\Models\Scopes;

use App\Models\Device;
use App\Models\Scopes\NewestUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class NewestUpdatedTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function it_orders_by_newest_updated_device()
    {
        $device1 = Device::factory()->create(['updated_at' => Carbon::now()->subDays(2)]);
        $device2 = Device::factory()->create(['updated_at' => Carbon::now()->subDays(1)]);
        $device3 = Device::factory()->create(['updated_at' => Carbon::now()->subDays(3)]);

        $devices = Collection::make([
            $device2,
            $device1,
            $device3,
        ]);

        $sorted = Device::scoped(new NewestUpdated())->get();

        $sorted->each(function(Device $device, int $index) use ($devices) {
            $this->assertSame($devices->get($index)->getKey(), $device->getKey());
        });
    }

    /** @test */
    public function it_orders_by_id_when_the_updated_at_is_the_same()
    {
        $date = Carbon::now()->toDateTimeString();
        $device1 = Device::factory()->create(['updated_at' => $date]);
        $device2 = Device::factory()->create(['updated_at' => $date]);
        $device3 = Device::factory()->create(['updated_at' => $date]);

        $devices = Collection::make([
            $device3,
            $device2,
            $device1,
        ]);

        $sorted = Device::scoped(new NewestUpdated())->get();

        $sorted->each(function(Device $device, int $index) use ($devices) {
            $this->assertSame($devices->get($index)->getKey(), $device->getKey());
        });
    }
}

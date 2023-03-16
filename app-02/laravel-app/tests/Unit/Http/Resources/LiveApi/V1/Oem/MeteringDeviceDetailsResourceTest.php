<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\Oem;

use App\Http\Resources\LiveApi\V1\Oem\MeteringDeviceDetailsResource;
use App\Models\Oem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class MeteringDeviceDetailsResourceTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function it_has_correct_fields()
    {
        $deviceType        = $this->faker->text(50);
        $devicesPerCircuit = $this->faker->numberBetween();
        $totalDevices      = $this->faker->numberBetween();
        $devicesSize       = $this->faker->text(100);

        $oem = Mockery::mock(Oem::class);
        $oem->shouldReceive('getAttribute')->withArgs(['device_type'])->once()->andReturn($deviceType);
        $oem->shouldReceive('getAttribute')->withArgs(['devices_per_circuit'])->once()->andReturn($devicesPerCircuit);
        $oem->shouldReceive('getAttribute')->withArgs(['total_devices'])->once()->andReturn($totalDevices);
        $oem->shouldReceive('getAttribute')->withArgs(['device_size'])->once()->andReturn($devicesSize);

        $resource = new MeteringDeviceDetailsResource($oem);
        $response = $resource->resolve();

        $expected = [
            'device_type'         => $deviceType,
            'devices_per_circuit' => $devicesPerCircuit,
            'total_devices'       => $totalDevices,
            'device_size'         => $devicesSize,
        ];

        $this->assertArrayHasKeysAndValues($expected, $response);
        $schema = $this->jsonSchema(MeteringDeviceDetailsResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}

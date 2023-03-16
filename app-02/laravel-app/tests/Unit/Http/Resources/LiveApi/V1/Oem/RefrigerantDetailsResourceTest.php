<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\Oem;

use App\Http\Resources\LiveApi\V1\Oem\RefrigerantDetailsResource;
use App\Models\Oem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class RefrigerantDetailsResourceTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function it_has_correct_fields()
    {
        $refrigerant      = $this->faker->text(100);
        $originalChargeOz = $this->faker->text(100);

        $oem = Mockery::mock(Oem::class);
        $oem->shouldReceive('getAttribute')->withArgs(['refrigerant'])->once()->andReturn($refrigerant);
        $oem->shouldReceive('getAttribute')->withArgs(['original_charge_oz'])->once()->andReturn($originalChargeOz);

        $resource = new RefrigerantDetailsResource($oem);
        $response = $resource->resolve();

        $expected = [
            'refrigerant'        => $refrigerant,
            'original_charge_oz' => $originalChargeOz,
        ];

        $this->assertArrayHasKeysAndValues($expected, $response);
        $schema = $this->jsonSchema(RefrigerantDetailsResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}

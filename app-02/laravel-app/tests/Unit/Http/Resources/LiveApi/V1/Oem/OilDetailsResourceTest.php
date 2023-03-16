<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\Oem;

use App\Http\Resources\LiveApi\V1\Oem\OilDetailsResource;
use App\Models\Oem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class OilDetailsResourceTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function it_has_correct_fields()
    {
        $oilType  = $this->faker->text(100);
        $oilAmtOz = $this->faker->text(50);
        $oilNotes = $this->faker->text();

        $oem = Mockery::mock(Oem::class);
        $oem->shouldReceive('getAttribute')->withArgs(['oil_type'])->once()->andReturn($oilType);
        $oem->shouldReceive('getAttribute')->withArgs(['oil_amt_oz'])->once()->andReturn($oilAmtOz);
        $oem->shouldReceive('getAttribute')->withArgs(['oil_notes'])->once()->andReturn($oilNotes);

        $resource = new OilDetailsResource($oem);
        $response = $resource->resolve();

        $expected = [
            'oil_type'   => $oilType,
            'oil_amt_oz' => $oilAmtOz,
            'oil_notes'  => $oilNotes,
        ];

        $this->assertArrayHasKeysAndValues($expected, $response);
        $schema = $this->jsonSchema(OilDetailsResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}

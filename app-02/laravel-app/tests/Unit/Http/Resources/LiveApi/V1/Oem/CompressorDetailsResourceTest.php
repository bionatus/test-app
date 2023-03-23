<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\Oem;

use App\Http\Resources\LiveApi\V1\Oem\CompressorDetailsResource;
use App\Models\Oem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class CompressorDetailsResourceTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function it_has_correct_fields()
    {
        $compressorType       = $this->faker->text(50);
        $compressorBrand      = $this->faker->text(50);
        $compressorModel      = $this->faker->text();
        $totalCompressor      = $this->faker->numberBetween();
        $compressorPerCircuit = $this->faker->numberBetween();
        $compressorSizes      = $this->faker->text(50);
        $rla                  = $this->faker->text(100);
        $lra                  = $this->faker->text();
        $capacityStaging      = $this->faker->text(50);
        $compressorNotes      = $this->faker->text();

        $oem = Mockery::mock(Oem::class);
        $oem->shouldReceive('getAttribute')->withArgs(['compressor_type'])->once()->andReturn($compressorType);
        $oem->shouldReceive('getAttribute')->withArgs(['compressor_brand'])->once()->andReturn($compressorBrand);
        $oem->shouldReceive('getAttribute')->withArgs(['compressor_model'])->once()->andReturn($compressorModel);
        $oem->shouldReceive('getAttribute')->withArgs(['total_compressors'])->once()->andReturn($totalCompressor);
        $oem->shouldReceive('getAttribute')
            ->withArgs(['compressors_per_circuit'])
            ->once()
            ->andReturn($compressorPerCircuit);
        $oem->shouldReceive('getAttribute')->withArgs(['compressor_sizes'])->once()->andReturn($compressorSizes);
        $oem->shouldReceive('getAttribute')->withArgs(['rla'])->once()->andReturn($rla);
        $oem->shouldReceive('getAttribute')->withArgs(['lra'])->once()->andReturn($lra);
        $oem->shouldReceive('getAttribute')->withArgs(['capacity_staging'])->once()->andReturn($capacityStaging);
        $oem->shouldReceive('getAttribute')->withArgs(['compressor_notes'])->once()->andReturn($compressorNotes);

        $resource = new CompressorDetailsResource($oem);
        $response = $resource->resolve();

        $expected = [
            'compressor_type'         => $compressorType,
            'compressor_brand'        => $compressorBrand,
            'compressor_model'        => $compressorModel,
            'total_compressors'       => $totalCompressor,
            'compressors_per_circuit' => $compressorPerCircuit,
            'compressor_sizes'        => $compressorSizes,
            'rla'                     => $rla,
            'lra'                     => $lra,
            'capacity_staging'        => $capacityStaging,
            'compressor_notes'        => $compressorNotes,
        ];

        $this->assertArrayHasKeysAndValues($expected, $response);
        $schema = $this->jsonSchema(CompressorDetailsResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}

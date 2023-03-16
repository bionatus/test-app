<?php

namespace Tests\Unit\Http\Resources\Api\V3\Part;

use App\Http\Resources\Api\V3\Part\SheaveAndPulleyResource;
use App\Models\SheaveAndPulley;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class SheaveAndPulleyResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_has_correct_fields()
    {
        $beltType          = $this->faker->text(10);
        $numberOfGrooves   = $this->faker->numberBetween();
        $boreDiameter      = $this->faker->text(10);
        $outsideDiameter   = $this->faker->randomFloat(2, 0, 100);
        $adjustable        = $this->faker->boolean();
        $boreMateType      = $this->faker->text(25);
        $bushingConnection = $this->faker->text(25);
        $keywayTypes       = $this->faker->text(25);
        $keywayHeight      = $this->faker->text(25);
        $keywayWidth       = $this->faker->text(25);
        $minimumDd         = $this->faker->randomFloat(2, 0, 100);
        $maximumDd         = $this->faker->randomFloat(2, 0, 100);
        $material          = $this->faker->text(100);

        $sheavePulley = Mockery::mock(SheaveAndPulley::class);
        $sheavePulley->shouldReceive('getAttribute')->withArgs(['belt_type'])->once()->andReturn($beltType);
        $sheavePulley->shouldReceive('getAttribute')
            ->withArgs(['number_of_grooves'])
            ->once()
            ->andReturn($numberOfGrooves);
        $sheavePulley->shouldReceive('getAttribute')->withArgs(['bore_diameter'])->once()->andReturn($boreDiameter);
        $sheavePulley->shouldReceive('getAttribute')
            ->withArgs(['outside_diameter'])
            ->once()
            ->andReturn($outsideDiameter);
        $sheavePulley->shouldReceive('getAttribute')->withArgs(['adjustable'])->once()->andReturn($adjustable);
        $sheavePulley->shouldReceive('getAttribute')->withArgs(['bore_mate_type'])->once()->andReturn($boreMateType);
        $sheavePulley->shouldReceive('getAttribute')
            ->withArgs(['bushing_connection'])
            ->once()
            ->andReturn($bushingConnection);
        $sheavePulley->shouldReceive('getAttribute')->withArgs(['keyway_types'])->once()->andReturn($keywayTypes);
        $sheavePulley->shouldReceive('getAttribute')->withArgs(['keyway_height'])->once()->andReturn($keywayHeight);
        $sheavePulley->shouldReceive('getAttribute')->withArgs(['keyway_width'])->once()->andReturn($keywayWidth);
        $sheavePulley->shouldReceive('getAttribute')->withArgs(['minimum_dd'])->once()->andReturn($minimumDd);
        $sheavePulley->shouldReceive('getAttribute')->withArgs(['maximum_dd'])->once()->andReturn($maximumDd);
        $sheavePulley->shouldReceive('getAttribute')->withArgs(['material'])->once()->andReturn($material);

        $resource = new SheaveAndPulleyResource($sheavePulley);

        $response = $resource->resolve();

        $data = [
            'belt_type'          => $beltType,
            'number_of_grooves'  => $numberOfGrooves,
            'bore_diameter'      => $boreDiameter,
            'outside_diameter'   => $outsideDiameter,
            'adjustable'         => $adjustable,
            'bore_mate_type'     => $boreMateType,
            'bushing_connection' => $bushingConnection,
            'keyway_types'       => $keywayTypes,
            'keyway_height'      => $keywayHeight,
            'keyway_width'       => $keywayWidth,
            'minimum_dd'         => $minimumDd,
            'maximum_dd'         => $maximumDd,
            'material'           => $material,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(SheaveAndPulleyResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}

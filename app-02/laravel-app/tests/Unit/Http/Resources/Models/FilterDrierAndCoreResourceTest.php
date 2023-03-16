<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\Models\FilterDrierAndCoreResource;
use App\Models\FilterDrierAndCore;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class FilterDrierAndCoreResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_has_correct_fields()
    {
        $volume               = $this->faker->numberBetween();
        $inletDiameter        = $this->faker->text(10);
        $inletConnectionType  = $this->faker->text(25);
        $outletDiameter       = $this->faker->text(10);
        $outletConnectionType = $this->faker->text(25);
        $directionOfFlow      = $this->faker->text(25);
        $desiccantType        = $this->faker->text(25);
        $numberOfCores        = $this->faker->numberBetween();
        $options              = $this->faker->text(50);
        $ratedCapacity        = $this->faker->text(25);

        $filterDrierAndCore = Mockery::mock(FilterDrierAndCore::class);
        $filterDrierAndCore->shouldReceive('getAttribute')->withArgs(['volume'])->once()->andReturn($volume);
        $filterDrierAndCore->shouldReceive('getAttribute')
            ->withArgs(['inlet_diameter'])
            ->once()
            ->andReturn($inletDiameter);
        $filterDrierAndCore->shouldReceive('getAttribute')
            ->withArgs(['inlet_connection_type'])
            ->once()
            ->andReturn($inletConnectionType);
        $filterDrierAndCore->shouldReceive('getAttribute')
            ->withArgs(['outlet_diameter'])
            ->once()
            ->andReturn($outletDiameter);
        $filterDrierAndCore->shouldReceive('getAttribute')
            ->withArgs(['outlet_connection_type'])
            ->once()
            ->andReturn($outletConnectionType);
        $filterDrierAndCore->shouldReceive('getAttribute')
            ->withArgs(['direction_of_flow'])
            ->once()
            ->andReturn($directionOfFlow);
        $filterDrierAndCore->shouldReceive('getAttribute')
            ->withArgs(['desiccant_type'])
            ->once()
            ->andReturn($desiccantType);
        $filterDrierAndCore->shouldReceive('getAttribute')
            ->withArgs(['number_of_cores'])
            ->once()
            ->andReturn($numberOfCores);
        $filterDrierAndCore->shouldReceive('getAttribute')->withArgs(['options'])->once()->andReturn($options);
        $filterDrierAndCore->shouldReceive('getAttribute')
            ->withArgs(['rated_capacity'])
            ->once()
            ->andReturn($ratedCapacity);

        $resource = new FilterDrierAndCoreResource($filterDrierAndCore);

        $response = $resource->resolve();

        $data = [
            'volume'                 => $volume,
            'inlet_diameter'         => $inletDiameter,
            'inlet_connection_type'  => $inletConnectionType,
            'outlet_diameter'        => $outletDiameter,
            'outlet_connection_type' => $outletConnectionType,
            'direction_of_flow'      => $directionOfFlow,
            'desiccant_type'         => $desiccantType,
            'number_of_cores'        => $numberOfCores,
            'options'                => $options,
            'rated_capacity'         => $ratedCapacity,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(FilterDrierAndCoreResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}

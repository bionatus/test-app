<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\Oem;

use App\Http\Resources\LiveApi\V1\Oem\SystemDetailsResource;
use App\Models\ModelType;
use App\Models\Oem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use PhpParser\Node\Expr\AssignOp\Mod;
use Tests\TestCase;

class SystemDetailsResourceTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function it_has_correct_fields()
    {
        $systemType    = $this->faker->text();
        $tonnage       = $this->faker->numberBetween();
        $totalCircuits = $this->faker->numberBetween();
        $dxChiller     = $this->faker->text(25);
        $coolingType   = $this->faker->text(50);
        $seer          = $this->faker->text(25);
        $eer           = $this->faker->text(25);

        $modelType = Mockery::mock(ModelType::class);
        $modelType->shouldReceive('getAttribute')->with('name')->once()->andReturn($modelTypeName = 'modelName');

        $oem = Mockery::mock(Oem::class);
        $oem->shouldReceive('getAttribute')->withArgs(['new_system_type'])->once()->andReturn($systemType);
        $oem->shouldReceive('getAttribute')->withArgs(['modelType'])->once()->andReturn($modelType);
        $oem->shouldReceive('getAttribute')->withArgs(['tonnage'])->once()->andReturn($tonnage);
        $oem->shouldReceive('getAttribute')->withArgs(['total_circuits'])->once()->andReturn($totalCircuits);
        $oem->shouldReceive('getAttribute')->withArgs(['dx_chiller'])->once()->andReturn($dxChiller);
        $oem->shouldReceive('getAttribute')->withArgs(['cooling_type'])->once()->andReturn($coolingType);
        $oem->shouldReceive('getAttribute')->withArgs(['seer'])->once()->andReturn($seer);
        $oem->shouldReceive('getAttribute')->withArgs(['eer'])->once()->andReturn($eer);

        $resource = new SystemDetailsResource($oem);
        $response = $resource->resolve();

        $expected = [
            'system_type'    => $systemType,
            'unit_type'      => $modelTypeName,
            'tonnage'        => $tonnage,
            'total_circuits' => $totalCircuits,
            'dx_chiller'     => $dxChiller,
            'cooling_type'   => $coolingType,
            'seer'           => $seer,
            'eer'            => $eer,
        ];

        $this->assertArrayHasKeysAndValues($expected, $response);
        $schema = $this->jsonSchema(SystemDetailsResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}

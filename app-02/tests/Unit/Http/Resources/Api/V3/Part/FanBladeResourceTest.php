<?php

namespace Tests\Unit\Http\Resources\Api\V3\Part;

use App\Http\Resources\Api\V3\Part\FanBladeResource;
use App\Models\FanBlade;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class FanBladeResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_has_correct_fields()
    {
        $diameter       = $this->faker->text(25);
        $numberOfBlades = $this->faker->numberBetween();
        $pitch          = $this->faker->text(25);
        $bore           = $this->faker->text(25);
        $rotation       = $this->faker->text(10);
        $rpm            = $this->faker->numberBetween();
        $cfm            = $this->faker->text(50);
        $bhp            = $this->faker->text(50);
        $material       = $this->faker->text(25);

        $fanBlade = Mockery::mock(FanBlade::class);
        $fanBlade->shouldReceive('getAttribute')->withArgs(['diameter'])->once()->andReturn($diameter);
        $fanBlade->shouldReceive('getAttribute')->withArgs(['number_of_blades'])->once()->andReturn($numberOfBlades);
        $fanBlade->shouldReceive('getAttribute')->withArgs(['pitch'])->once()->andReturn($pitch);
        $fanBlade->shouldReceive('getAttribute')->withArgs(['bore'])->once()->andReturn($bore);
        $fanBlade->shouldReceive('getAttribute')->withArgs(['rotation'])->once()->andReturn($rotation);
        $fanBlade->shouldReceive('getAttribute')->withArgs(['rpm'])->once()->andReturn($rpm);
        $fanBlade->shouldReceive('getAttribute')->withArgs(['cfm'])->once()->andReturn($cfm);
        $fanBlade->shouldReceive('getAttribute')->withArgs(['bhp'])->once()->andReturn($bhp);
        $fanBlade->shouldReceive('getAttribute')->withArgs(['material'])->once()->andReturn($material);

        $resource = new FanBladeResource($fanBlade);

        $response = $resource->resolve();

        $data = [
            'diameter'         => $diameter,
            'number_of_blades' => $numberOfBlades,
            'pitch'            => $pitch,
            'bore'             => $bore,
            'rotation'         => $rotation,
            'rpm'              => $rpm,
            'cfm'              => $cfm,
            'bhp'              => $bhp,
            'material'         => $material,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(FanBladeResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}

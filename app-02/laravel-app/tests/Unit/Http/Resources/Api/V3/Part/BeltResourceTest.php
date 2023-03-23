<?php

namespace Tests\Unit\Http\Resources\Api\V3\Part;

use App\Http\Resources\Api\V3\Part\BeltResource;
use App\Models\Belt;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class BeltResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_has_correct_fields()
    {
        $family     = $this->faker->text(25);
        $beltType   = $this->faker->text(25);
        $beltLength = $this->faker->text(50);
        $pitch      = $this->faker->text(10);
        $thickness  = $this->faker->text(50);
        $topWidth   = $this->faker->text(50);

        $belt = Mockery::mock(Belt::class);
        $belt->shouldReceive('getAttribute')->withArgs(['family'])->once()->andReturn($family);
        $belt->shouldReceive('getAttribute')->withArgs(['belt_type'])->once()->andReturn($beltType);
        $belt->shouldReceive('getAttribute')->withArgs(['belt_length'])->once()->andReturn($beltLength);
        $belt->shouldReceive('getAttribute')->withArgs(['pitch'])->once()->andReturn($pitch);
        $belt->shouldReceive('getAttribute')->withArgs(['thickness'])->once()->andReturn($thickness);
        $belt->shouldReceive('getAttribute')->withArgs(['top_width'])->once()->andReturn($topWidth);

        $resource = new BeltResource($belt);

        $response = $resource->resolve();

        $data = [
            'family'      => $family,
            'belt_type'   => $beltType,
            'belt_length' => $beltLength,
            'pitch'       => $pitch,
            'thickness'   => $thickness,
            'top_width'   => $topWidth,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BeltResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}

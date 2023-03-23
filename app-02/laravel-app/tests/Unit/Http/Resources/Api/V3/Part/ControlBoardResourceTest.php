<?php

namespace Tests\Unit\Http\Resources\Api\V3\Part;

use App\Http\Resources\Api\V3\Part\ControlBoardResource;
use App\Models\ControlBoard;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class ControlBoardResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_has_correct_fields()
    {
        $fused = $this->faker->boolean();

        $controlBoard = Mockery::mock(ControlBoard::class);
        $controlBoard->shouldReceive('getAttribute')->withArgs(['fused'])->once()->andReturn($fused);

        $resource = new ControlBoardResource($controlBoard);

        $response = $resource->resolve();

        $data = [
            'fused' => $fused,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ControlBoardResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}

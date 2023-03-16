<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\Models\OtherResource;
use App\Models\Other;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class OtherResourceTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_has_correct_fields()
    {
        $sort = $this->faker->numberBetween();

        $other = Mockery::mock(Other::class);
        $other->shouldReceive('getAttribute')->withArgs(['sort'])->once()->andReturn($sort);

        $resource = new OtherResource($other);

        $response = $resource->resolve();

        $data = [
            'sort' => $sort,
        ];
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(OtherResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}

<?php

namespace Tests\Unit\Http\Resources\Api\V3\Oem;

use App\Http\Resources\Api\V3\Oem\WarningResource;
use App\Models\Warning;
use Mockery;
use Tests\TestCase;

class WarningResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $warning = Mockery::mock(Warning::class);
        $warning->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 1);
        $warning->shouldReceive('getAttribute')->with('title')->once()->andReturn($title = 'title');
        $warning->shouldReceive('getAttribute')->with('description')->once()->andReturn($description = 'description');

        $resource = new WarningResource($warning);

        $response = $resource->resolve();

        $data = [
            'id'          => $id,
            'title'       => $title,
            'description' => $description,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(WarningResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}

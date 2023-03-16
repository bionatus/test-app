<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Constants\MediaCollectionNames;
use App\Http\Resources\Models\ModelTypeResource;
use App\Models\ModelType;
use Mockery;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Tests\TestCase;

class ModelTypeResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $modelType = Mockery::mock(ModelType::class);
        $modelType->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 'route key');
        $modelType->shouldReceive('getAttribute')->withArgs(['name'])->once()->andReturn($name = 'a name');
        $modelType->shouldReceive('getMedia')
            ->withArgs([MediaCollectionNames::IMAGES])
            ->once()
            ->andReturn(new MediaCollection([]));

        $resource = new ModelTypeResource($modelType);

        $response = $resource->resolve();

        $data = [
            'id'    => $id,
            'name'  => $name,
            'image' => null,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ModelTypeResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}

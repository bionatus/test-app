<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\Oem;

use App\Http\Resources\LiveApi\V1\Oem\TagResource;
use App\Http\Resources\Models\ImageResource;
use App\Models\Media;
use App\Types\TaggableType;
use Exception;
use Mockery;
use Tests\TestCase;

class TagResourceTest extends TestCase
{
    /** @test
     * @throws Exception
     */
    public function it_has_correct_fields()
    {
        $id   = 'id';
        $name = 'name';
        $type = 'model_type';

        $taggableType = new TaggableType([
            'id'   => $id,
            'name' => $name,
            'type' => $type,
        ]);

        $resource = new TagResource($taggableType);

        $response = $resource->resolve();

        $data = [
            'id'    => $id,
            'name'  => $name,
            'type'  => $type,
            'image' => null,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(TagResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test
     * @throws Exception
     */
    public function it_has_correct_fields_with_image()
    {
        $id    = 'id';
        $name  = 'name';
        $type  = 'model_type';
        $media = Mockery::mock(Media::class);
        $media->shouldReceive('getAttribute')->withArgs(['uuid'])->once()->andReturn('uuid');
        $media->shouldReceive('getUrl')->withNoArgs()->once()->andReturn('url');
        $media->shouldReceive('hasGeneratedConversion')->withAnyArgs()->once()->andReturnFalse();

        $taggableType = new TaggableType([
            'id'    => $id,
            'name'  => $name,
            'type'  => $type,
            'media' => $media,
        ]);

        $resource = new TagResource($taggableType);

        $response = $resource->resolve();

        $data = [
            'id'    => $id,
            'name'  => $name,
            'type'  => $type,
            'image' => new ImageResource($media),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(TagResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}

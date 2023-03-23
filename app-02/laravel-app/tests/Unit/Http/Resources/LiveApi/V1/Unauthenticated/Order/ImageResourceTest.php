<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\Unauthenticated\Order;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\LiveApi\V1\Unauthenticated\Order\ImageResource;
use App\Models\Media;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class ImageResourceTest extends TestCase
{
    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(ImageResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields()
    {
        $media = Mockery::mock(Media::class);
        $media->shouldReceive('getUrl')->withNoArgs()->once()->andReturn($url = 'media url');
        $media->shouldReceive('getUrl')->withArgs(['thumb'])->once()->andReturn($thumbUrl = 'media thumb url');
        $media->shouldReceive('getAttribute')->withArgs(['uuid'])->once()->andReturn($uuid = 'media uuid');
        $media->shouldReceive('hasGeneratedConversion')->withAnyArgs()->once()->andReturn(true);

        $resource = new ImageResource($media);
        $response = $resource->resolve();
        $expected = [
            'id'          => $uuid,
            'url'         => $url,
            'conversions' => [
                'thumb' => $thumbUrl,
            ],
        ];

        $this->assertArrayHasKeysAndValues($expected, $response);
        $schema = $this->jsonSchema(ImageResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_does_not_return_thumb_url_if_thumb_conversion_generation_failed()
    {
        $media = Mockery::mock(Media::class);
        $media->shouldReceive('getUrl')->withNoArgs()->once()->andReturn($url = 'media url');
        $media->shouldReceive('getAttribute')->withArgs(['uuid'])->once()->andReturn($uuid = 'media uuid');
        $media->shouldReceive('hasGeneratedConversion')->withAnyArgs()->once()->andReturn(false);

        $resource = new ImageResource($media);
        $response = $resource->resolve();
        $data     = [
            'id'          => $uuid,
            'url'         => $url,
            'conversions' => [],
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ImageResource::jsonSchema(true), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}

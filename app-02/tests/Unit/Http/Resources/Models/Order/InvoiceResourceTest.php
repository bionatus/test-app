<?php

namespace Tests\Unit\Http\Resources\Models\Order;

use App\Http\Resources\Models\Order\InvoiceResource;
use App\Models\Media;
use Mockery;
use Tests\TestCase;

class InvoiceResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $media = Mockery::mock(Media::class);
        $media->shouldReceive('getUrl')->withNoArgs()->once()->andReturn($url = 'media url');
        $media->shouldReceive('getAttribute')->withArgs(['uuid'])->once()->andReturn($uuid = 'media uuid');

        $resource = new InvoiceResource($media);

        $response = $resource->resolve();

        $data = [
            'id'  => $uuid,
            'url' => $url,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(InvoiceResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_can_be_null()
    {
        $response = null;

        $schema = $this->jsonSchema(InvoiceResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}

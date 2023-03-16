<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\User\Order;

use App\Http\Resources\LiveApi\V1\User\Order\ImageResource;
use Tests\TestCase;

class ImageResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $image = [
            [
                'id'         => 'an id',
                'url'        => 'the image url',
                "size"       => 7375,
                "type"       => "image/png",
                "width"      => 120,
                "height"     => 60,
                "filename"   => "filename",
                'thumbnails' => [
                    'full'  => [
                        'url'    => 'the full thumbnail url',
                        "width"  => 120,
                        "height" => 60,
                    ],
                    'large' => [
                        'url'    => 'the large thumbnail url',
                        "width"  => 120,
                        "height" => 60,
                    ],
                    'small' => [
                        'url'    => 'the small thumbnail url',
                        "width"  => 120,
                        "height" => 60,
                    ],
                ],
            ],
        ];

        $resource = new ImageResource($image);
        $response = $resource->resolve();

        $data = [
            'id'          => $image[0]['id'],
            'url'         => $image[0]['url'],
            'conversions' => [
                'thumb' => $image[0]['thumbnails']['full']['url'],
            ],
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ImageResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_when_there_is_no_thumb()
    {
        $image = [
            [
                'id'         => 'an id',
                'url'        => 'the image url',
                "size"       => 7375,
                "type"       => "image/png",
                "width"      => 120,
                "height"     => 60,
                "filename"   => "filename",
                'thumbnails' => [
                    'large' => [
                        'url'    => 'the large thumbnail url',
                        "width"  => 120,
                        "height" => 60,
                    ],
                    'small' => [
                        'url'    => 'the small thumbnail url',
                        "width"  => 120,
                        "height" => 60,
                    ],
                ],
            ],
        ];

        $resource = new ImageResource($image);
        $response = $resource->resolve();

        $data = [
            'id'          => $image[0]['id'],
            'url'         => $image[0]['url'],
            'conversions' => [],
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ImageResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}

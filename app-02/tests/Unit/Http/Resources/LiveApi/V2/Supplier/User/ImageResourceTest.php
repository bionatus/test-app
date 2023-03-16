<?php

namespace Tests\Unit\Http\Resources\LiveApi\V2\Supplier\User;

use App\Constants\MediaConversionNames;
use App\Http\Resources\LiveApi\V2\Supplier\User\ImageResource;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImageResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $user  = User::factory()->create();
        $media = Media::factory()->post()->create([
            'generated_conversions' => [MediaConversionNames::THUMB => true],
            'model_id'              => $user->getKey(),
            'model_type'            => User::MORPH_ALIAS,
        ])->fresh();

        $resource = new ImageResource($user);
        $response = $resource->resolve();

        $expected = [
            'id'          => $media->uuid,
            'url'         => $media->getUrl(),
            'conversions' => [
                'thumb' => $media->getUrl(MediaConversionNames::THUMB),
            ],
        ];

        $this->assertArrayHasKeysAndValues($expected, $response);
        $schema = $this->jsonSchema(ImageResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_does_not_return_thumb_url_if_thumb_conversion_generation_failed()
    {
        $user  = User::factory()->create();
        $media = Media::factory()->create([
            'model_id'   => $user->getKey(),
            'model_type' => User::MORPH_ALIAS,
        ])->fresh();

        $resource = new ImageResource($user);
        $response = $resource->resolve();

        $data = [
            'id'          => $media->uuid,
            'url'         => $media->getUrl(),
            'conversions' => [],
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(ImageResource::jsonSchema(true), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}

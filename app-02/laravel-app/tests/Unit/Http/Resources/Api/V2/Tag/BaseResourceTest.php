<?php

namespace Tests\Unit\Http\Resources\Api\V2\Tag;

use App\Http\Resources\Api\V2\Tag\BaseResource;
use App\Models\Tag;
use App\Types\TaggableType;
use Exception;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /**
     * @test
     * @throws Exception
     */
    public function it_has_correct_fields()
    {
        $rawTag = new TaggableType([
            'id'   => 'the-id',
            'type' => Tag::TYPE_GENERAL,
            'name' => 'name',
        ]);

        $resource = new BaseResource($rawTag);

        $response = $resource->resolve();

        $data = [
            'id'   => $rawTag->id,
            'type' => $rawTag->type,
            'name' => $rawTag->name,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /**
     * @test
     * @throws Exception
     */
    public function it_allows_images()
    {
        $rawTag = new TaggableType([
            'id'   => 'the-id',
            'type' => Tag::TYPE_GENERAL,
            'name' => 'name',
        ]);

        $response = (new BaseResource($rawTag))->toArrayWithAdditionalData(['images' => []]);

        $data = [
            'id'     => $rawTag->id,
            'type'   => $rawTag->type,
            'name'   => $rawTag->name,
            'images' => [],
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}

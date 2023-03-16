<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\Oem;

use App\Http\Resources\LiveApi\V1\Oem\TagCollection;
use App\Http\Resources\LiveApi\V1\Oem\TagResource;
use App\Models\ModelType;
use App\Models\Oem;
use App\Models\Series;
use App\Models\SeriesSystem;
use App\Models\Tag;
use App\Types\TaggableType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagCollectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $series = Series::factory()->create();
        $modelType = ModelType::factory()->create();
        Oem::factory()->usingSeries($series)->usingModelType($modelType)->create();
        $page         = TaggableType::query(Tag::TYPE_MODEL_TYPE, $series->getRouteKey())->paginate(15);

        $resource = new TagCollection($page);
        $response = $resource->resolve();

        $data = [
            'data'           => TagResource::collection($page),
            'has_more_pages' => $page->hasMorePages(),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(TagCollection::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}

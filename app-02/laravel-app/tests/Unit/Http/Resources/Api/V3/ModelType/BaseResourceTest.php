<?php

namespace Tests\Unit\Http\Resources\Api\V3\ModelType;

use App\Http\Resources\Api\V3\ModelType\BaseResource;
use App\Models\ModelType;
use App\Models\Oem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Str;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $name = 'Package Unit';
        $slug = Str::slug($name);

        $modelType = ModelType::factory()->create([
            'name' => $name,
        ]);

        Oem::factory()->count(2)->usingModelType($modelType)->create();

        $resource = new BaseResource($modelType);
        $response = $resource->resolve();

        $data = [
            'id'         => $slug,
            'name'       => $name,
            'image'      => null,
            'oems_count' => 2,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}

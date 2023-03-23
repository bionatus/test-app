<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\Models\GenericReplacementResource;
use App\Models\ItemOrder;
use Tests\TestCase;

class GenericReplacementResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $type        = ItemOrder::REPLACEMENT_TYPE_GENERIC;
        $description = 'Fake description';

        $resource = new GenericReplacementResource($description);

        $response = $resource->resolve();

        $data = [
            'type'        => $type,
            'description' => $description,
        ];
        
        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(GenericReplacementResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}

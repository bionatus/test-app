<?php

namespace Tests\Unit\Http\Resources\Api\Nova\JobTitle;

use App\Http\Resources\Api\Nova\JobTitle\JobTitleResource;
use Tests\TestCase;

class JobTitleResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $resource = new JobTitleResource($jobTitle = 'A job title');
        $response = $resource->resolve();

        $data = [
            'value'   => $jobTitle,
            'display' => $jobTitle,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(JobTitleResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}

<?php

namespace Tests\Unit\Http\Resources\Api\V3\User\Count;

use App\Http\Resources\Api\V3\User\Count\BaseResource;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /** @test
     * @dataProvider dataProvider
     */
    public function it_has_correct_fields(int $quantity, string $count)
    {
        $resource = new BaseResource($quantity);

        $response = $resource->resolve();

        $data = ['users_count' => $count];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    public function dataProvider(): array
    {
        return [
            [1, '1'],
            [100, '100'],
            [1000, '1k'],
            [1400, '1.4k'],
            [2000000, '2M'],
        ];
    }
}

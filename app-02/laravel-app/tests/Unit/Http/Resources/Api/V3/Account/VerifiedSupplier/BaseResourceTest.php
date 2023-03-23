<?php

namespace Tests\Unit\Http\Resources\Api\V3\Account\VerifiedSupplier;

use App\Http\Resources\Api\V3\Account\VerifiedSupplier\BaseResource;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /**
     * @test
     * @dataProvider dataProvider
     */
    public function it_has_correct_fields(int $quantity, bool $expectedValue)
    {
        $resource = new BaseResource($quantity);

        $response = $resource->resolve();

        $data = ['has_verified_suppliers' => $expectedValue];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    public function dataProvider(): array
    {
        return [
            [0, false],
            [1, true],
            [100, true],
        ];
    }
}

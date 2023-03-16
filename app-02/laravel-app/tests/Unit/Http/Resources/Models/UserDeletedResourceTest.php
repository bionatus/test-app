<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\Models\UserDeletedResource;
use App\Models\OrderLockedData;
use Mockery;
use Tests\TestCase;

class UserDeletedResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $orderLockedData = Mockery::mock(OrderLockedData::class);
        $orderLockedData->shouldReceive('getAttribute')
            ->withArgs(['user_first_name'])
            ->once()
            ->andReturn($firstName = 'John');
        $orderLockedData->shouldReceive('getAttribute')
            ->withArgs(['user_public_name'])
            ->once()
            ->andReturn($publicName = 'Johnny');
        $orderLockedData->shouldReceive('getAttribute')
            ->withArgs(['user_last_name'])
            ->once()
            ->andReturn($lastName = 'Doe');
        $orderLockedData->shouldReceive('getAttribute')
            ->withArgs(['user_company'])
            ->once()
            ->andReturn($companyName = 'Company Name');

        $resource = new UserDeletedResource($orderLockedData);

        $response = $resource->resolve();

        $data = [
            'id'          => null,
            'first_name'  => $firstName,
            'last_name'   => $lastName,
            'public_name' => $publicName,
            'photo'       => null,
            'company'     => $companyName,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(UserDeletedResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}

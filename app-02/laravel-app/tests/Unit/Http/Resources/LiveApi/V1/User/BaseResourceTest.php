<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\User;

use App\Http\Resources\LiveApi\V1\User\BaseResource;
use App\Http\Resources\LiveApi\V1\User\ExtendedSupplierUserResource;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {

        $confirmedUsers   = Collection::make([]);
        $unconfirmedUsers = Collection::make([]);

        $resource = new BaseResource($confirmedUsers, $unconfirmedUsers);

        $response = $resource->resolve();

        $data = [
            'confirmedUsers'   => ExtendedSupplierUserResource::collection(Collection::make([])),
            'unconfirmedUsers' => ExtendedSupplierUserResource::collection(Collection::make([])),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}

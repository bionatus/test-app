<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\User\Order;

use App\Http\Resources\LiveApi\V1\User\Order\UserResource;
use App\Models\User;
use Mockery;
use Storage;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $id         = 123;
        $firstName  = 'John';
        $lastName   = 'Doe';
        $publicName = 'Johnny';
        $photo      = null;

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id);
        $user->shouldReceive('getAttribute')->withArgs(['first_name'])->once()->andReturn($firstName);
        $user->shouldReceive('getAttribute')->withArgs(['last_name'])->once()->andReturn($lastName);
        $user->shouldReceive('getAttribute')->withArgs(['public_name'])->once()->andReturn($publicName);
        $user->shouldReceive('getAttribute')->withArgs(['photo'])->once()->andReturn($photo);
        $user->shouldReceive('companyName')->withNoArgs()->once()->andReturn($company = 'company');
        $user->shouldReceive('isDisabled')->withNoArgs()->once()->andReturnFalse();

        $resource = new UserResource($user);

        $response = $resource->resolve();

        $data = [
            'id'          => $id,
            'first_name'  => $firstName,
            'last_name'   => $lastName,
            'public_name' => $publicName,
            'photo'       => $photo,
            'company'     => $company,
            'disabled'    => false,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(UserResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_all_data()
    {
        $id         = 123;
        $firstName  = 'John';
        $lastName   = 'Doe';
        $publicName = 'Johnny';
        $photo      = 'photo';

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id);
        $user->shouldReceive('getAttribute')->withArgs(['first_name'])->once()->andReturn($firstName);
        $user->shouldReceive('getAttribute')->withArgs(['last_name'])->once()->andReturn($lastName);
        $user->shouldReceive('getAttribute')->withArgs(['public_name'])->once()->andReturn($publicName);
        $user->shouldReceive('getAttribute')->withArgs(['photo'])->once()->andReturn($photo);
        $user->shouldReceive('companyName')->withNoArgs()->once()->andReturn($company = 'company');
        $user->shouldReceive('isDisabled')->withNoArgs()->once()->andReturnTrue();

        $finalUrl = 'valid.jpg';
        Storage::shouldReceive('url')->with($photo)->andReturn($finalUrl);

        $resource = new UserResource($user);

        $response = $resource->resolve();

        $data = [
            'id'          => $id,
            'first_name'  => $firstName,
            'last_name'   => $lastName,
            'public_name' => $publicName,
            'photo'       => $finalUrl,
            'company'     => $company,
            'disabled'    => true,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(UserResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}

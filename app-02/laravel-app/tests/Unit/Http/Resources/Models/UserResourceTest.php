<?php

namespace Tests\Unit\Http\Resources\Models;

use App\Http\Resources\Models\UserResource;
use App\Models\User;
use Mockery;
use Storage;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->withArgs(['first_name'])->once()->andReturn($firstName = 'John');
        $user->shouldReceive('getAttribute')->withArgs(['last_name'])->once()->andReturn($lastName = 'Doe');
        $user->shouldReceive('getAttribute')->withArgs(['public_name'])->once()->andReturn($publicName = 'Johnny');

        $user->shouldReceive('getAttribute')->withArgs(['photo'])->once()->andReturnNull();
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 123);
        $user->shouldReceive('isDisabled')->withNoArgs()->once()->andReturnFalse();

        $resource = new UserResource($user);

        $response = $resource->resolve();

        $data = [
            'id'          => $id,
            'first_name'  => $firstName,
            'last_name'   => $lastName,
            'public_name' => $publicName,
            'photo'       => null,
            'disabled'    => false,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(UserResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_all_data()
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->withArgs(['first_name'])->once()->andReturn($firstName = 'John');
        $user->shouldReceive('getAttribute')->withArgs(['last_name'])->once()->andReturn($lastName = 'Doe');
        $user->shouldReceive('getAttribute')->withArgs(['public_name'])->once()->andReturn($publicName = 'Johnny');
        $user->shouldReceive('getAttribute')->withArgs(['photo'])->once()->andReturn($photo = 'photo.jpg');
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 123);
        $user->shouldReceive('isDisabled')->withNoArgs()->once()->andReturnTrue();

        Storage::shouldReceive('url')->with($photo)->andReturn($photoUrl = 'photoUrl.jpg');

        $resource = new UserResource($user);

        $response = $resource->resolve();

        $data = [
            'id'          => $id,
            'first_name'  => $firstName,
            'last_name'   => $lastName,
            'public_name' => $publicName,
            'photo'       => $photoUrl,
            'disabled'    => true,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(UserResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}

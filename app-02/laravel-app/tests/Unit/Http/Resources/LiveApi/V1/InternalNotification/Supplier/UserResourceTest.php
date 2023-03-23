<?php

namespace Tests\Unit\Http\Resources\LiveApi\V1\InternalNotification\Supplier;

use App\Http\Resources\LiveApi\V1\InternalNotification\Supplier\UserResource;
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
        $user->shouldReceive('getAttribute')->with('first_name')->once()->andReturn($firstName = 'John');
        $user->shouldReceive('getAttribute')->with('last_name')->once()->andReturn($lastName = 'Doe');
        $user->shouldReceive('fullName')->withNoArgs()->once()->andReturn($fullName = 'John Doe');
        $user->shouldReceive('getAttribute')->withArgs(['public_name'])->once()->andReturn($publicName = 'Johnny');
        $user->shouldReceive('getAttribute')->withArgs(['experience_years'])->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->withArgs(['photo'])->once()->andReturnNull();
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 1);
        $user->shouldReceive('companyName')->withNoArgs()->once()->andReturnNull();
        $user->shouldReceive('isDisabled')->withNoArgs()->once()->andReturnFalse();

        $resource = new UserResource($user);
        $response = $resource->resolve();
        $data     = [
            'id'          => $id,
            'first_name'  => $firstName,
            'last_name'   => $lastName,
            'name'        => $fullName,
            'public_name' => $publicName,
            'company'     => null,
            'experience'  => null,
            'photo'       => null,
            'disabled'    => false,
        ];
        $schema   = $this->jsonSchema(UserResource::jsonSchema(), false, false);

        $this->assertEquals($data, $response);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_all_data()
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('first_name')->once()->andReturn($firstName = 'John');
        $user->shouldReceive('getAttribute')->with('last_name')->once()->andReturn($lastName = 'Doe');
        $user->shouldReceive('fullName')->withNoArgs()->once()->andReturn($fullName = 'John Doe');
        $user->shouldReceive('getAttribute')->withArgs(['public_name'])->once()->andReturn($publicName = 'Johnny');
        $user->shouldReceive('getAttribute')->withArgs(['experience_years'])->once()->andReturn($experienceYears = 8);
        $user->shouldReceive('getAttribute')->withArgs(['photo'])->once()->andReturn($photoUrl = 'photoUrl.jpg');
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 1);
        $user->shouldReceive('companyName')->withNoArgs()->once()->andReturn($company = 'company');
        $user->shouldReceive('isDisabled')->withNoArgs()->once()->andReturnTrue();

        $resource = new UserResource($user);
        $response = $resource->resolve();
        $data     = [
            'id'          => $id,
            'first_name'  => $firstName,
            'last_name'   => $lastName,
            'name'        => $fullName,
            'public_name' => $publicName,
            'company'     => $company,
            'experience'  => $experienceYears,
            'photo'       => Storage::url($photoUrl),
            'disabled'    => true,
        ];
        $schema   = $this->jsonSchema(UserResource::jsonSchema(), false, false);

        $this->assertEquals($data, $response);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}

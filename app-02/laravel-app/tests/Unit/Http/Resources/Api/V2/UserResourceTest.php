<?php

namespace Tests\Unit\Http\Resources\Api\V2;

use App\Http\Resources\Api\V2\UserResource;
use App\Http\Resources\Types\CountryResource;
use App\Http\Resources\Types\StateResource;
use App\Models\User;
use App\Types\CountryDataType;
use MenaraSolutions\Geographer\Country;
use MenaraSolutions\Geographer\State;
use Mockery;
use Storage;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $user = Mockery::mock(User::class);
        $user->shouldNotReceive('getAttribute')->withArgs(['state']);
        $user->shouldReceive('fullName')->withNoArgs()->once()->andReturn($name = 'full name');
        $user->shouldReceive('getAttribute')->with('public_name')->once()->andReturn($publicName = 'public name');
        $user->shouldReceive('getAttribute')->with('company')->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->with('experience_years')->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->with('photo')->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->with('city')->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->with('country')->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->with('verified_at')->once()->andReturnNull();
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 1);

        $resource = new UserResource($user);
        $response = $resource->resolve();
        $data     = [
            'id'          => $id,
            'name'        => $name,
            'city'        => null,
            'state'       => null,
            'country'     => null,
            'public_name' => $publicName,
            'company'     => null,
            'experience'  => null,
            'photo'       => null,
            'verified'    => false,
        ];
        $schema   = $this->jsonSchema(UserResource::jsonSchema(), false, false);

        $this->assertEquals($data, $response);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_data()
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('fullName')->withNoArgs()->once()->andReturn($name = 'full name');
        $user->shouldReceive('getAttribute')->with('public_name')->once()->andReturn($publicName = 'public name');
        $user->shouldReceive('getAttribute')->with('company')->once()->andReturn($company = 'company');
        $user->shouldReceive('getAttribute')->with('experience_years')->once()->andReturn($experience = 8);
        $user->shouldReceive('getAttribute')->with('photo')->twice()->andReturn($photo = 'photoUrl.jpg');
        $user->shouldReceive('getAttribute')->with('city')->once()->andReturn($city = 'New York');
        $user->shouldReceive('getAttribute')
            ->withArgs(['state'])
            ->andReturn($stateCode = CountryDataType::UNITED_STATES . '-AR');
        $user->shouldReceive('getAttribute')
            ->withArgs(['country'])
            ->once()
            ->andReturn($countryCode = CountryDataType::UNITED_STATES);
        $user->shouldReceive('getAttribute')->with('verified_at')->once()->andReturn('verified_at');
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 1);

        $country = Country::build($countryCode);
        $state   = $country->getStates()->filter(fn(State $state) => $state->isoCode === $stateCode)->first();

        $resource = new UserResource($user);
        $response = $resource->resolve();
        $data     = [
            'id'          => $id,
            'name'        => $name,
            'city'        => $city,
            'state'       => new StateResource($state),
            'country'     => new CountryResource($country),
            'public_name' => $publicName,
            'company'     => $company,
            'experience'  => $experience,
            'photo'       => Storage::url($photo),
            'verified'    => true,
        ];
        $schema   = $this->jsonSchema(UserResource::jsonSchema(), false, false);

        $this->assertEquals($data, $response);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}

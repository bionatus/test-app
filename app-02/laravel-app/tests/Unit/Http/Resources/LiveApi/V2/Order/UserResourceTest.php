<?php

namespace Tests\Unit\Http\Resources\LiveApi\V2\Order;

use App\Http\Resources\HasJsonSchema;
use App\Http\Resources\LiveApi\V2\Order\UserResource;
use App\Models\Device;
use App\Models\PushNotificationToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use ReflectionClass;
use Storage;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(UserResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields()
    {
        $devices = Mockery::mock(Collection::class);
        $devices->shouldReceive('first')->withNoArgs()->once()->andReturnNull();

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->withArgs(['devices'])->once()->andReturn($devices);
        $user->shouldReceive('getAttribute')->withArgs(['first_name'])->once()->andReturn($firstName = 'John');
        $user->shouldReceive('getAttribute')->withArgs(['last_name'])->once()->andReturn($lastName = 'Doe');
        $user->shouldReceive('getAttribute')->withArgs(['public_name'])->once()->andReturn($publicName = 'Johnny');
        $user->shouldReceive('getAttribute')->withArgs(['photo'])->once()->andReturnNull();
        $user->shouldReceive('photoUrl')->withNoArgs()->once()->andReturnNull();
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 123);
        $user->shouldReceive('companyName')->withNoArgs()->once()->andReturnNull();
        $user->shouldReceive('isDisabled')->withNoArgs()->once()->andReturnFalse();

        $resource = new UserResource($user);

        $response = $resource->resolve();

        $data = [
            'id'                      => $id,
            'first_name'              => $firstName,
            'last_name'               => $lastName,
            'public_name'             => $publicName,
            'photo'                   => null,
            'company'                 => null,
            'disabled'                => false,
            'push_notification_token' => null,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(UserResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_all_data()
    {
        $pushNotificationToken = Mockery::mock(PushNotificationToken::class);
        $pushNotificationToken->shouldReceive('getAttribute')->withArgs(['token'])->once()->andReturn($token = 'token');

        $device = Mockery::mock(Device::class);
        $device->shouldReceive('getAttribute')
            ->withArgs(['pushNotificationToken'])
            ->once()
            ->andReturn($pushNotificationToken);

        $devices = Mockery::mock(Collection::class);
        $devices->shouldReceive('first')->withNoArgs()->once()->andReturn($device);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('companyName')->withNoArgs()->once()->andReturn($company = 'Company name');
        $user->shouldReceive('getAttribute')->withArgs(['devices'])->once()->andReturn($devices);
        $user->shouldReceive('getAttribute')->withArgs(['first_name'])->once()->andReturn($firstName = 'John');
        $user->shouldReceive('getAttribute')->withArgs(['last_name'])->once()->andReturn($lastName = 'Doe');
        $user->shouldReceive('getAttribute')->withArgs(['public_name'])->once()->andReturn($publicName = 'Johnny');
        $user->shouldReceive('getAttribute')->withArgs(['photo'])->once()->andReturn($photo = 'photo.jpg');
        $user->shouldReceive('photoUrl')->withNoArgs()->once()->andReturn($photoUrl = asset(Storage::url($photo)));
        $user->shouldReceive('isDisabled')->withNoArgs()->once()->andReturnTrue();
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 123);

        $resource = new UserResource($user);
        $response = $resource->resolve();

        $data = [
            'id'                      => $id,
            'first_name'              => $firstName,
            'last_name'               => $lastName,
            'public_name'             => $publicName,
            'photo'                   => $photoUrl,
            'company'                 => $company,
            'disabled'                => true,
            'push_notification_token' => $token,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(UserResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}

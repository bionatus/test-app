<?php

namespace Tests\Unit\Http\Resources\Api\V3\Account;

use App\Http\Resources\Api\V3\Account\AppVersionResource;
use App\Http\Resources\Api\V3\Account\DetailedResource;
use App\Http\Resources\Api\V3\Account\SettingCollection;
use App\Http\Resources\HasJsonSchema;
use App\Models\AppVersion;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Mockery;
use ReflectionClass;
use Tests\TestCase;

class DetailedResourceTest extends TestCase
{
    /** @test */
    public function it_implements_has_json_schema()
    {
        $reflection = new ReflectionClass(DetailedResource::class);

        $this->assertTrue($reflection->implementsInterface(HasJsonSchema::class));
    }

    /** @test */
    public function it_has_correct_fields_with_null_values()
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('hasToSAccepted')->withNoArgs()->once()->andReturnTrue();
        $user->shouldReceive('isAccredited')->withNoArgs()->once()->andReturnTrue();
        $user->shouldReceive('isRegistered')->withNoArgs()->once()->andReturnTrue();
        $user->shouldReceive('isVerified')->withNoArgs()->once()->andReturnTrue();
        $user->shouldReceive('getAttribute')->with('address')->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->with('bio')->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->with('city')->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->with('country')->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->with('experience_years')->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->with('first_name')->once()->andReturn($firstName = 'first_name');
        $user->shouldReceive('getAttribute')->with('last_name')->once()->andReturn($lastName = 'last_name');
        $user->shouldReceive('getAttribute')
            ->with('manual_download_count')
            ->once()
            ->andReturn($manualDownloadCount = 1);
        $user->shouldReceive('getAttribute')->with('photo')->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->with('state')->once()->andReturnNull();
        $user->shouldReceive('getAttribute')->with('timezone')->once()->andReturnNull();
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 1);
        $user->shouldReceive('getUnreadNotificationsCount')->withNoArgs()->once()->andReturn($notificationsCount = 1);
        $user->shouldReceive('allSettingUsers')
            ->withAnyArgs()
            ->once()
            ->andReturn($allSettingUsers = Collection::make());

        $appVersion = Mockery::mock(AppVersion::class);
        $appVersion->shouldReceive('getAttribute')->with('current')->once()->andReturn('2.0.0');
        $appVersion->shouldReceive('getAttribute')->with('min')->once()->andReturn('1.2.3');
        $appVersion->shouldReceive('getAttribute')->with('video_title')->once()->andReturnNull();
        $appVersion->shouldReceive('getAttribute')->with('video_url')->once()->andReturn('video_url');
        $appVersion->shouldReceive('getAttribute')->with('message')->once()->andReturn('message');
        $appVersion->shouldReceive('needsConfirm')->with($clientVersion = '0.0.0', $user)->once()->andReturnTrue();
        $appVersion->shouldReceive('needsUpdate')->with($clientVersion)->once()->andReturnTrue();

        $resource = new DetailedResource($user, $token = 'token', $appVersion, $clientVersion);
        $response = $resource->resolve();

        $data = [
            'id'                     => $id,
            'first_name'             => $firstName,
            'last_name'              => $lastName,
            'accredited'             => true,
            'registration_completed' => true,
            'photo'                  => null,
            'tos_accepted'           => true,
            'notifications_count'    => $notificationsCount,
            'verified'               => true,
            'manual_download_count'  => $manualDownloadCount,
            'token'                  => $token,
            'address'                => null,
            'city'                   => null,
            'state'                  => null,
            'country'                => null,
            'timezone'               => null,
            'bio'                    => null,
            'experience'             => null,
            'settings'               => new SettingCollection($allSettingUsers),
            'app_version'            => new AppVersionResource($appVersion, $clientVersion, $user),
        ];

        $this->assertEquals($data, $response);

        $schema = $this->jsonSchema(DetailedResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }

    /** @test */
    public function it_has_correct_fields_with_data()
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('hasToSAccepted')->withNoArgs()->once()->andReturnFalse();
        $user->shouldReceive('isAccredited')->withNoArgs()->once()->andReturnFalse();
        $user->shouldReceive('isRegistered')->withNoArgs()->once()->andReturnFalse();
        $user->shouldReceive('isVerified')->withNoArgs()->once()->andReturnFalse();
        $user->shouldReceive('getAttribute')->with('address')->once()->andReturn($address = 'address');
        $user->shouldReceive('getAttribute')->with('bio')->once()->andReturn($bio = 'bio');
        $user->shouldReceive('getAttribute')->with('city')->once()->andReturn($city = 'city');
        $user->shouldReceive('getAttribute')->with('country')->once()->andReturn($country = 'country');
        $user->shouldReceive('getAttribute')
            ->with('experience_years')
            ->once()
            ->andReturn($experience = 'experience_years');
        $user->shouldReceive('getAttribute')->with('first_name')->once()->andReturn($firstName = 'first_name');
        $user->shouldReceive('getAttribute')->with('last_name')->once()->andReturn($lastName = 'last_name');
        $user->shouldReceive('getAttribute')
            ->with('manual_download_count')
            ->once()
            ->andReturn($manualDownloadCount = 1);
        $user->shouldReceive('getAttribute')->with('photo')->twice()->andReturn($photo = 'photo');
        $user->shouldReceive('getAttribute')->with('state')->once()->andReturn($state = 'state');
        $user->shouldReceive('getAttribute')->with('timezone')->once()->andReturn($timezone = 'timezone');
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id = 1);
        $user->shouldReceive('getUnreadNotificationsCount')->withNoArgs()->once()->andReturn($notificationsCount = 1);
        $user->shouldReceive('allSettingUsers')
            ->withAnyArgs()
            ->once()
            ->andReturn($allSettingUsers = Collection::make());

        $appVersion = Mockery::mock(AppVersion::class);
        $appVersion->shouldReceive('getAttribute')->with('current')->once()->andReturn('2.0.0');
        $appVersion->shouldReceive('getAttribute')->with('message')->once()->andReturn('message');
        $appVersion->shouldReceive('getAttribute')->with('min')->once()->andReturn('1.2.3');
        $appVersion->shouldReceive('getAttribute')->with('video_title')->once()->andReturnNull();
        $appVersion->shouldReceive('getAttribute')->with('video_url')->once()->andReturn('video_url');
        $appVersion->shouldReceive('needsConfirm')->with($clientVersion = '0.0.0', $user)->once()->andReturnFalse();
        $appVersion->shouldReceive('needsUpdate')->with($clientVersion)->once()->andReturnFalse();

        $resource = new DetailedResource($user, $token = 'token', $appVersion, $clientVersion);
        $response = $resource->resolve();

        $data = [
            'id'                     => $id,
            'first_name'             => $firstName,
            'last_name'              => $lastName,
            'accredited'             => false,
            'registration_completed' => false,
            'photo'                  => Storage::url($photo),
            'tos_accepted'           => false,
            'notifications_count'    => $notificationsCount,
            'verified'               => false,
            'manual_download_count'  => $manualDownloadCount,
            'token'                  => $token,
            'address'                => $address,
            'city'                   => $city,
            'state'                  => $state,
            'country'                => $country,
            'timezone'               => $timezone,
            'bio'                    => $bio,
            'experience'             => $experience,
            'settings'               => new SettingCollection($allSettingUsers),
            'app_version'            => new AppVersionResource($appVersion, $clientVersion, $user),
        ];

        $this->assertEquals($data, $response);

        $schema = $this->jsonSchema(DetailedResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}

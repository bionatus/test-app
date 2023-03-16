<?php

namespace Tests\Unit\Http\Resources\Api\V3\Account;

use App\Http\Resources\Api\V3\Account\BaseResource;
use App\Models\User;
use Mockery;
use Storage;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    /** @test */
    public function it_has_correct_fields()
    {
        $id                    = 123;
        $firstName             = 'John';
        $lastName              = 'Doe';
        $accredited            = false;
        $registrationCompleted = false;
        $photo                 = 'valid.jpg';
        $tosAccepted           = false;
        $notificationsCount    = 12;
        $verified              = true;
        $manualDownloadCount   = 5;
        $token                 = 'a valid token';

        $user = Mockery::mock(User::class);
        $user->shouldReceive('getRouteKey')->withNoArgs()->once()->andReturn($id);
        $user->shouldReceive('getAttribute')->withArgs(['first_name'])->once()->andReturn($firstName);
        $user->shouldReceive('getAttribute')->withArgs(['last_name'])->once()->andReturn($lastName);
        $user->shouldReceive('isAccredited')->withNoArgs()->once()->andReturn($accredited);
        $user->shouldReceive('isRegistered')->withNoArgs()->once()->andReturn($registrationCompleted);
        $user->shouldReceive('getAttribute')->withArgs(['photo'])->times(3)->andReturn($photo);
        $user->shouldReceive('hasToSAccepted')->withNoArgs()->once()->andReturn($tosAccepted);
        $user->shouldReceive('getUnreadNotificationsCount')->withNoArgs()->once()->andReturn($notificationsCount);
        $user->shouldReceive('isVerified')->withNoArgs()->once()->andReturn($verified);
        $user->shouldReceive('getAttribute')
            ->withArgs(['manual_download_count'])
            ->once()
            ->andReturn($manualDownloadCount);

        Storage::shouldReceive('url')->with($user->photo)->andReturn($photo);

        $resource = new BaseResource($user, $token);

        $response = $resource->resolve();

        $data = [
            'id'                     => $id,
            'first_name'             => $firstName,
            'last_name'              => $lastName,
            'accredited'             => $accredited,
            'registration_completed' => $registrationCompleted,
            'photo'                  => $photo,
            'tos_accepted'           => $tosAccepted,
            'notifications_count'    => $notificationsCount,
            'verified'               => $verified,
            'manual_download_count'  => $manualDownloadCount,
            'token'                  => $token,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}

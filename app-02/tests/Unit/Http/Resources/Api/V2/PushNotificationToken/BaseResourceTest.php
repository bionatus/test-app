<?php

namespace Tests\Unit\Http\Resources\Api\V2\PushNotificationToken;

use App\Http\Resources\Api\V2\PushNotificationToken\BaseResource;
use App\Models\Device;
use App\Models\PushNotificationToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BaseResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fields()
    {
        $device = Device::factory()->create(['udid' => $udid = 'a valid device']);

        $pushNotificationToken = PushNotificationToken::factory()->usingDevice($device)->create();

        $resource = new BaseResource($pushNotificationToken);

        $response = $resource->resolve();

        $data = [
            'id'         => $pushNotificationToken->getRouteKey(),
            'os'         => $pushNotificationToken->os,
            'device'     => $udid,
            'updated_at' => $pushNotificationToken->updated_at,
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}

<?php

namespace Tests\Unit\Http\Resources\Api\V2\Twilio\Token;

use App\Http\Resources\Api\V2\Twilio\Token\BaseResource;
use Tests\TestCase;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\VoiceGrant;

class BaseResourceTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_correct_fields()
    {
        $token = new AccessToken('sid', 'key', 'secret', 3600, 1);

        $token->addGrant(new VoiceGrant());

        $resource = new BaseResource($token);

        $response = $resource->resolve();

        $data = [
            'token' => $token->toJWT(),
        ];

        $this->assertEquals($data, $response);
        $schema = $this->jsonSchema(BaseResource::jsonSchema(), false, false);
        $this->assertValidSchema($schema, json_decode(json_encode($response)));
    }
}

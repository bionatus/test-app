<?php

namespace Tests\Unit\Services\Communication\Auth\Providers\Twilio\Call;

use App\Models\AuthenticationCode;
use App\Services\Communication\Auth\Call\ResponseInterface;
use App\Services\Communication\Auth\Providers\Twilio\Call\Response;
use ReflectionClass;
use Tests\TestCase;

class ResponseTest extends TestCase
{
    /** @test */
    public function it_implements_response_interface()
    {
        $reflection = new ReflectionClass(Response::class);

        $this->assertTrue($reflection->implementsInterface(ResponseInterface::class));
    }

    /** @test */
    public function it_returns_a_hangup_response()
    {
        $response = new Response();

        $rawResponse = $response->hangUp();
        $this->assertIsString($rawResponse);

        $rawTwiml    = simplexml_load_string($rawResponse);
        $objectTwiml = json_decode(json_encode($rawTwiml));
        $this->assertObjectHasAttribute('Hangup', $objectTwiml);
    }

    /** @test */
    public function it_returns_a_technical_difficulties_response()
    {
        $response = new Response();

        $rawResponse = $response->technicalDifficulties();
        $this->assertIsString($rawResponse);

        $rawTwiml    = simplexml_load_string($rawResponse);
        $objectTwiml = json_decode(json_encode($rawTwiml));
        $this->assertObjectHasAttribute('Say', $objectTwiml);
        $this->assertEquals('We are very sorry, currently we are experiencing technical difficulties. Please contact us at a later time.',
            $objectTwiml->Say);
    }

    /** @test */
    public function it_returns_a_say_code_response()
    {
        $response = new Response();

        $authenticationCode = AuthenticationCode::factory()->make(['phone_id' => 1]);

        $rawResponse = $response->sayCode($authenticationCode);
        $this->assertIsString($rawResponse);
    }
}

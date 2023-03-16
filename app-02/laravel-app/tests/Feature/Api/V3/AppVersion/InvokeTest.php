<?php

namespace Tests\Feature\Api\V3\AppVersion;

use App\Constants\RequestKeys;
use App\Constants\RouteNames;
use App\Http\Controllers\Api\V3\AppVersionController;
use App\Http\Resources\Api\V3\AppVersion\BaseResource;
use App\Models\AppVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use URL;

/** @see AppVersionController */
class InvokeTest extends TestCase
{
    use RefreshDatabase;

    private string $routeName = RouteNames::API_V3_APP_VERSION;

    /** @test */
    public function it_returns_the_app_version()
    {
        AppVersion::factory()->create([
            'min'         => $min = '1.0.0',
            'current'     => $current = '2.0.0',
            'video_title' => $videoTitle = 'video title',
            'video_url'   => $videoUrl = 'video url',
            'message'     => $message = 'message',
        ]);

        $response = $this->getWithParameters(URL::route($this->routeName), [RequestKeys::VERSION => '0.0.0']);

        $response->assertStatus(Response::HTTP_OK);
        $this->validateResponseSchema($this->jsonSchema(BaseResource::jsonSchema()), $response);

        $expectedData = [
            'min'             => $min,
            'current'         => $current,
            'video_title'     => $videoTitle,
            'video_url'       => $videoUrl,
            'message'         => $message,
            'requires_update' => true,
        ];
        $data         = $response->json('data');

        $this->assertSame($expectedData, $data);
    }
}

<?php

namespace Tests\Feature\Nova\Resources\AppVersion;

use App\Models\AppVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lang;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Nova\TestCase;

class ValidationTest extends TestCase
{
    use RefreshDatabase;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = '/nova-api/' . \App\Nova\Resources\AppVersion::uriKey() . DIRECTORY_SEPARATOR;
    }

    /** @test */
    public function its_min_is_required_when_updating()
    {
        $appVersion = AppVersion::factory()->create();
        $response   = $this->putJson($this->path . $appVersion->getKey(), []);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'min' => Lang::get('validation.required', ['attribute' => 'Min']),
        ]);
    }

    /** @test */
    public function its_min_must_be_a_version_number_when_updating()
    {
        $appVersion = AppVersion::factory()->create();
        $response   = $this->putJson($this->path . $appVersion->getKey(), ['min' => 'invalid-version']);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'min' => Lang::get('validation.regex', ['attribute' => 'Min']),
        ]);
    }

    /** @test */
    public function its_current_is_required_when_updating()
    {
        $appVersion = AppVersion::factory()->create();
        $response   = $this->putJson($this->path . $appVersion->getKey(), []);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'current' => Lang::get('validation.required', ['attribute' => 'Current']),
        ]);
    }

    /** @test */
    public function its_current_must_be_a_version_number_when_updating()
    {
        $appVersion = AppVersion::factory()->create();
        $response   = $this->putJson($this->path . $appVersion->getKey(), ['current' => 'invalid-version']);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'current' => Lang::get('validation.regex', ['attribute' => 'Current']),
        ]);
    }

    /** @test */
    public function its_video_url_must_be_an_url_when_updating()
    {
        $appVersion = AppVersion::factory()->create();
        $response   = $this->putJson($this->path . $appVersion->getKey(), ['video_url' => 'foo']);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'video_url' => Lang::get('validation.url', ['attribute' => 'Video URL']),
        ]);
    }

    /** @test */
    public function its_message_is_required_when_updating()
    {
        $appVersion = AppVersion::factory()->create();
        $response   = $this->putJson($this->path . $appVersion->getKey(), []);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'message' => Lang::get('validation.required', ['attribute' => 'Message']),
        ]);
    }

    /** @test */
    public function it_should_pass_validation_on_valid_data_when_updating()
    {
        $appSetting = AppVersion::factory()->create();
        $response   = $this->putJson($this->path . $appSetting->getKey(), [
            'min'         => '0.0.0',
            'current'     => '2.0.0',
            'video_title' => 'Video title',
            'video_url'   => 'http://video.url',
            'message'     => 'Fake message',
        ]);
        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_OK);
    }
}

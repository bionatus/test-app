<?php

namespace Tests\Feature\Nova\Resources\AppSetting;

use App\Models\AppSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lang;
use Str;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Nova\TestCase;

class ValidationTest extends TestCase
{
    use RefreshDatabase;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = '/nova-api/' . \App\Nova\Resources\AppSetting::uriKey() . DIRECTORY_SEPARATOR;
    }

    /** @test */
    public function its_value_is_required_when_updating()
    {
        $appSetting = AppSetting::factory()->create();
        $response   = $this->putJson($this->path . $appSetting->getKey(), []);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'value' => Lang::get('validation.required', ['attribute' => 'value']),
        ]);
    }

    /** @test */
    public function its_value_must_have_less_than_255_characters_when_updating()
    {
        $value = Str::random(256);

        $appSetting = AppSetting::factory()->create();
        $response   = $this->putJson($this->path . $appSetting->getKey(), ['value' => $value]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'value' => Lang::get('validation.max.string', ['attribute' => 'value', 'max' => 255]),
        ]);
    }

    /** @test */
    public function it_should_pass_validation_on_valid_data_when_updating()
    {
        $appSetting = AppSetting::factory()->create();
        $response   = $this->putJson($this->path . $appSetting->getKey(), [
            'value' => 'App Setting value',
        ]);
        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_OK);
    }
}

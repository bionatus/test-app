<?php

namespace Tests\Feature\Nova\Resources\Instrument;

use App\Models\Instrument as InstrumentModel;
use App\Nova\Resources\Instrument;
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

        $this->path = '/nova-api/' . Instrument::uriKey() . '/';
    }

    /** @test */
    public function its_name_is_required_when_creating()
    {
        $response = $this->postJson($this->path, []);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'name' => Lang::get('validation.required', ['attribute' => 'Name']),
        ]);
    }

    /** @test */
    public function its_name_is_required_when_updating()
    {
        $instrument = InstrumentModel::factory()->create();
        $response   = $this->putJson($this->path . $instrument->getKey(), []);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'name' => Lang::get('validation.required', ['attribute' => 'Name']),
        ]);
    }

    /** @test */
    public function its_name_must_have_less_than_256_characters_when_creating()
    {
        $response = $this->postJson($this->path, ['name' => Str::random(256)]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'name' => Lang::get('validation.max.string', ['attribute' => 'Name', 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_name_must_have_less_than_256_characters_when_updating()
    {
        $instrument = InstrumentModel::factory()->create();
        $response   = $this->putJson($this->path . $instrument->getKey(), [
            'name' => Str::random(256),
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'name' => Lang::get('validation.max.string', ['attribute' => 'Name', 'max' => 255]),
        ]);
    }

    /** @test */
    public function it_should_pass_validation_on_valid_data_on_creation()
    {
        $response = $this->postJson($this->path, [
            'name' => 'new name',
        ]);

        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_CREATED);
    }

    /** @test */
    public function it_should_pass_validation_on_valid_data_on_update()
    {
        $instrument = InstrumentModel::factory()->create();
        $response   = $this->putJson($this->path . $instrument->getKey(), ['name' => 'new name']);

        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_OK);
    }
}

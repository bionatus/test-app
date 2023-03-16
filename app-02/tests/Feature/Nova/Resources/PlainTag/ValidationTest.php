<?php

namespace Tests\Feature\Nova\Resources\PlainTag;

use App\Models\PlainTag;
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

        $this->path = '/nova-api/' . \App\Nova\Resources\PlainTag::uriKey() . DIRECTORY_SEPARATOR;
    }

    /** @test */
    public function its_name_is_required_when_creating()
    {
        $response = $this->postJson($this->path, []);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'name' => Lang::get('validation.required', ['attribute' => 'Title']),
        ]);
    }

    /** @test */
    public function its_name_is_required_when_updating()
    {
        $tag      = PlainTag::factory()->create();
        $response = $this->putJson($this->path . $tag->getKey(), []);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'name' => Lang::get('validation.required', ['attribute' => 'Title']),
        ]);
    }

    /** @test */
    public function its_name_must_have_less_than_50_characters_when_creating()
    {
        $name = Str::random(51);

        $response = $this->postJson($this->path, ['name' => $name]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'name' => Lang::get('validation.max.string', ['attribute' => 'Title', 'max' => 50]),
        ]);
    }

    /** @test */
    public function its_name_must_have_less_than_50_characters_when_updating()
    {
        $name = Str::random(51);

        $tag      = PlainTag::factory()->more()->create();
        $response = $this->putJson($this->path . $tag->getKey(), ['name' => $name]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'name' => Lang::get('validation.max.string', ['attribute' => 'Title', 'max' => 50]),
        ]);
    }

    /** @test */
    public function it_should_pass_validation_on_valid_data()
    {
        $response = $this->postJson($this->path, [
            'name' => 'Tag Title',
        ]);
        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_CREATED);
    }
}

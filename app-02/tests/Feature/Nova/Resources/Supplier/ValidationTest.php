<?php

namespace Tests\Feature\Nova\Resources\Supplier;

use App;
use App\Models\Supplier;
use App\Services\Hubspot\Hubspot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lang;
use Mockery;
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

        $this->path = '/nova-api/' . App\Nova\Resources\Supplier::uriKey() . DIRECTORY_SEPARATOR;
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
        $supplier = Supplier::factory()->createQuietly();
        $response = $this->putJson($this->path . $supplier->getKey(), []);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'name' => Lang::get('validation.required', ['attribute' => 'Name']),
        ]);
    }

    /** @test */
    public function its_name_must_have_less_than_256_characters_when_creating()
    {
        $name = Str::random(256);

        $response = $this->postJson($this->path, ['name' => $name]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'name' => Lang::get('validation.max.string', ['attribute' => 'Name', 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_name_must_have_less_than_256_characters_when_updating()
    {
        $name = Str::random(256);

        $supplier = Supplier::factory()->createQuietly();
        $response = $this->putJson($this->path . $supplier->getKey(), ['name' => $name]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'name' => Lang::get('validation.max.string', ['attribute' => 'Name', 'max' => 255]),
        ]);
    }

    /** @test */
    public function it_should_pass_validation_on_valid_data()
    {
        $hubspot = Mockery::mock(Hubspot::class);
        $hubspot->shouldReceive('upsertCompany')->withAnyArgs()->once()->andReturnNull();
        App::bind(Hubspot::class, fn() => $hubspot);

        $response = $this->postJson($this->path, [
            'name'            => 'A name',
            'email'           => 'store@email.com',
            'password'        => 'password123',
            'take_rate'       => 300,
            'take_rate_until' => '2023-01-12',
            'terms'           => '2.5%/10 Net 90',
        ]);
        $response->assertJsonMissingValidationErrors();
        $response->assertStatus(Response::HTTP_CREATED);
    }

    /** @test */
    public function its_terms_must_have_less_than_256_characters_when_creating()
    {
        $terms = Str::random(256);

        $response = $this->postJson($this->path, ['terms' => $terms]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'terms' => Lang::get('validation.max.string', ['attribute' => 'Terms', 'max' => 255]),
        ]);
    }

    /** @test */
    public function its_terms_must_have_less_than_256_characters_when_updating()
    {
        $terms = Str::random(256);

        $supplier = Supplier::factory()->createQuietly();
        $response = $this->putJson($this->path . $supplier->getKey(), ['terms' => $terms]);
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors([
            'terms' => Lang::get('validation.max.string', ['attribute' => 'Terms', 'max' => 255]),
        ]);
    }
}
